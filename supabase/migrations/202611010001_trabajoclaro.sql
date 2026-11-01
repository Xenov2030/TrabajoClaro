begin;
create schema if not exists tc_private;
revoke all on schema tc_private from public, anon, authenticated;
create table public.tc_businesses (
 id uuid primary key default gen_random_uuid(), owner_id uuid not null unique references auth.users(id),
 data jsonb not null, next_quote integer not null default 1, is_demo boolean not null default false,
 expires_at timestamptz, created_at timestamptz not null default now()
);
create table public.tc_records (
 id uuid primary key default gen_random_uuid(), business_id uuid not null references public.tc_businesses(id) on delete cascade,
 kind text not null check(kind in ('customer','quote','version','job','payment','activity','metric')),
 data jsonb not null, revision integer not null default 1, created_at timestamptz not null default now(),
 unique(business_id,id)
);
create index on public.tc_records(business_id,kind);
create unique index tc_job_quote on public.tc_records(business_id,(data->>'quote_id')) where kind='job' and data->>'quote_id' is not null;
create unique index tc_payment_key on public.tc_records(business_id,(data->>'key')) where kind='payment';
create table public.tc_public_versions (
 token_hash text primary key, business_id uuid not null references public.tc_businesses(id) on delete cascade,
 quote_id uuid not null, version_id uuid not null unique, snapshot jsonb not null,
 expires_on date not null, timezone text not null, status text not null default 'pendiente',
 decision jsonb, created_at timestamptz not null default now(),
 foreign key(business_id,quote_id) references public.tc_records(business_id,id),
 foreign key(business_id,version_id) references public.tc_records(business_id,id)
);
create table public.tc_feedback (
 id uuid primary key default gen_random_uuid(), screen text not null, release text not null,
 description text not null, contact text, created_at timestamptz not null default now()
);
create table public.tc_pilot_requests (
 id uuid primary key default gen_random_uuid(), name text not null, email text not null, message text not null, created_at timestamptz not null default now()
);
alter table public.tc_pilot_requests enable row level security;
revoke all on public.tc_pilot_requests from public,anon,authenticated;
create unique index tc_metric_dedupe on public.tc_records(business_id,(data->>'dedupe')) where kind='metric';
create table tc_private.limits (key text primary key, started_at timestamptz not null, count integer not null);
alter table public.tc_businesses enable row level security;
alter table public.tc_records enable row level security;
alter table public.tc_public_versions enable row level security;
alter table public.tc_feedback enable row level security;
revoke all on public.tc_businesses,public.tc_records,public.tc_public_versions,public.tc_feedback from public,anon,authenticated;
grant select on public.tc_businesses,public.tc_records to authenticated;
create policy tc_business_owner on public.tc_businesses for select to authenticated using(owner_id=(select auth.uid()) and (expires_at is null or expires_at>now()));
create policy tc_record_owner on public.tc_records for select to authenticated using(exists(select 1 from public.tc_businesses b where b.id=business_id and b.owner_id=(select auth.uid()) and (b.expires_at is null or b.expires_at>now())));

create function tc_private.need(ok boolean, message text) returns void language plpgsql as $$begin if not coalesce(ok,false) then raise exception using errcode='22023',message=message; end if; end$$;
create function tc_private.text_value(d jsonb,k text,maximum integer,required boolean default true) returns text language plpgsql as $$declare v text:=btrim(coalesce(d->>k,''));begin perform tc_private.need(length(v)<=maximum and (not required or length(v)>0),'Revisá el campo '||k||'.');return v;end$$;
create function tc_private.decimal_value(v text,places integer default 2) returns numeric language plpgsql as $$begin perform tc_private.need(v ~ ('^\d{1,9}(\.\d{1,'||places||'})?$'),'Usá punto decimal, sin separadores de miles.');return v::numeric;end$$;
create function tc_private.quote(d jsonb) returns jsonb language plpgsql as $$
declare subtotal bigint:=0; line bigint; discount bigint; tax bigint; total bigint; advance bigint; rate numeric; i jsonb; items jsonb:='[]'; issued date; expires date;
begin
 perform tc_private.text_value(d,'title',160);perform tc_private.text_value(d,'scope',5000);perform tc_private.text_value(d,'exclusions',3000,false);perform tc_private.text_value(d,'conditions',3000,false);
 perform tc_private.need(d->>'currency' in ('ARS','USD'),'Moneda inválida.');
 issued:=(d->>'issued_on')::date;expires:=(d->>'expires_on')::date;perform tc_private.need(expires>=issued,'Revisá las fechas.');
 perform tc_private.need(jsonb_typeof(d->'items')='array' and jsonb_array_length(d->'items') between 1 and 30,'Agregá entre 1 y 30 ítems.');
 for i in select value from jsonb_array_elements(d->'items') loop
  perform tc_private.text_value(i,'description',500);perform tc_private.need(coalesce(i->>'category','') in ('','material','mano de obra'),'Categoría inválida.');
  perform tc_private.need(tc_private.decimal_value(i->>'quantity',3)>0,'La cantidad debe ser mayor a cero.');
  line:=round(tc_private.decimal_value(i->>'quantity',3)*tc_private.decimal_value(i->>'unit_price')*100);subtotal:=subtotal+line;
  perform tc_private.need(subtotal<=99999999999,'El subtotal supera el máximo.');items:=items||jsonb_build_array(i||jsonb_build_object('total_minor',line));
 end loop;
 discount:=tc_private.decimal_value(coalesce(nullif(d->>'discount',''),'0'))*100;rate:=tc_private.decimal_value(coalesce(nullif(d->>'tax_rate',''),'0'));
 perform tc_private.need(discount<=subtotal and rate<=100,'Revisá descuento e impuesto.');tax:=round((subtotal-discount)*rate/100);total:=subtotal-discount+tax;
 advance:=tc_private.decimal_value(coalesce(nullif(d->>'advance',''),'0'))*100;perform tc_private.need(total<=99999999999 and advance<=total,'Revisá total y anticipo.');
 return d||jsonb_build_object('items',items,'subtotal_minor',subtotal,'discount_minor',discount,'tax_minor',tax,'total_minor',total,'advance_minor',advance);
end$$;
create function tc_private.limit_request(k text, maximum integer, seconds integer) returns void language plpgsql as $$declare n integer;begin
 insert into tc_private.limits values(k,now(),1) on conflict(key) do update set count=case when limits.started_at<now()-make_interval(secs=>seconds) then 1 else limits.count+1 end,started_at=case when limits.started_at<now()-make_interval(secs=>seconds) then now() else limits.started_at end returning count into n;
 perform tc_private.need(n<=maximum,'Demasiados intentos. Esperá antes de reintentar.');end$$;
create function tc_private.event(b uuid, label text, subject uuid, details jsonb default '{}') returns void language plpgsql as $$
declare event_name text; dedupe text;
begin
 insert into public.tc_records(business_id,kind,data) values(b,'activity',jsonb_build_object('name',label,'subject_id',subject,'details',details));
 if (select is_demo from public.tc_businesses where id=b) then return;end if;
 event_name:=case label when 'Registro completado' then 'registration_completed' when 'Borrador guardado' then 'first_quote_created' when 'Versión publicada' then 'first_version_published' when 'Trabajo creado' then 'job_created' when 'Cobro registrado' then 'first_payment_recorded' when 'Decisión registrada' then case when details->>'decision'='aceptado' then 'quote_accepted' end end;
 if event_name is null then return;end if;
 dedupe:=case when event_name like 'first_%' or event_name='registration_completed' then event_name else gen_random_uuid()::text end;
 insert into public.tc_records(business_id,kind,data) values(b,'metric',jsonb_build_object('name',event_name,'dedupe',dedupe)) on conflict do nothing;
end$$;

-- Every mutation is authorized here, inside one PostgreSQL transaction. No service-role key in the web app.
create function public.tc_command(action text,payload jsonb default '{}') returns jsonb language plpgsql security definer set search_path='' as $$
declare actor uuid:=auth.uid();b public.tc_businesses; r public.tc_records; c public.tc_records; j public.tc_records; old public.tc_records; v public.tc_public_versions; id uuid; d jsonb; token text; minor bigint; collected bigint; result jsonb; today date; version_no integer;
begin
 perform tc_private.need(actor is not null,'Iniciá sesión para continuar.');perform tc_private.need(octet_length(payload::text)<100000,'El formulario es demasiado grande.');
 if action='setup' then
  perform tc_private.text_value(payload,'name',120);perform tc_private.text_value(payload,'industry',100);
  perform tc_private.need(payload->>'currency' in ('ARS','USD'),'Moneda inválida.');perform tc_private.need(exists(select 1 from pg_timezone_names where name=payload->>'timezone'),'Zona horaria inválida.');
  perform tc_private.need(length(payload->>'country')=2,'País inválido.');
  insert into public.tc_businesses(owner_id,data,is_demo,expires_at) values(actor,payload,coalesce((auth.jwt()->>'is_anonymous')::boolean,false),case when coalesce((auth.jwt()->>'is_anonymous')::boolean,false) then now()+interval '24 hours' end) on conflict(owner_id) do nothing returning * into b;
  if b.id is not null then perform tc_private.event(b.id,'Registro completado',b.id);end if; select * into b from public.tc_businesses where owner_id=actor;return to_jsonb(b);
 end if;
 select * into b from public.tc_businesses where owner_id=actor for update;
 perform tc_private.need(b.id is not null and (b.expires_at is null or b.expires_at>now()),'Configurá el negocio o iniciá otra demo si venció.');
 today:=(now() at time zone (b.data->>'timezone'))::date;
 if action='settings' then
  perform tc_private.text_value(payload,'name',120);perform tc_private.text_value(payload,'industry',100);perform tc_private.need(payload->>'currency' in ('ARS','USD') and length(payload->>'country')=2,'País o moneda inválidos.');perform tc_private.need(exists(select 1 from pg_timezone_names where name=payload->>'timezone'),'Zona horaria inválida.');
  update public.tc_businesses set data=payload where id=b.id;return payload;
 end if;
 if payload->>'id' is not null then select * into r from public.tc_records where tc_records.id=(payload->>'id')::uuid and business_id=b.id for update;perform tc_private.need(r.id is not null,'No encontramos ese registro en tu negocio.');end if;
 if action='customer' then
  d:=payload->'data';perform tc_private.text_value(d,'name',120);perform tc_private.text_value(d,'email',190,false);perform tc_private.text_value(d,'notes',2000,false);perform tc_private.text_value(d,'address',500,false);perform tc_private.need(coalesce(d->>'email','')='' or d->>'email' ~ '^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$','Correo inválido.');
  perform tc_private.need(coalesce(d->>'phone','')='' or d->>'phone' ~ '^\+[1-9][0-9]{7,14}$','Usá teléfono internacional, por ejemplo +5491112345678.');
  if r.id is null then insert into public.tc_records(business_id,kind,data) values(b.id,'customer',d) returning tc_records.id into id;
  else perform tc_private.need(r.kind='customer','Registro inválido.');update public.tc_records set data=d,revision=revision+1 where tc_records.id=r.id;id:=r.id;end if;
 elsif action='archive' then
  perform tc_private.need(r.kind='customer','Registro inválido.');update public.tc_records set data=data||jsonb_build_object('archived_at',now()) where tc_records.id=r.id;id:=r.id;
 elsif action in ('quote','duplicate') then
  if action='duplicate' then perform tc_private.need(r.kind='quote','Registro inválido.');d:=r.data||jsonb_build_object('issued_on',today,'expires_on',today+15);r.id:=null;else d:=payload->'data';end if;
  d:=tc_private.quote(d);select * into c from public.tc_records where tc_records.id=(d->>'customer_id')::uuid and business_id=b.id and kind='customer';perform tc_private.need(c.id is not null and c.data->>'archived_at' is null,'Elegí un cliente activo de tu negocio.');
  if r.id is null then
   d:=d||jsonb_build_object('number',b.next_quote,'status','borrador');insert into public.tc_records(business_id,kind,data) values(b.id,'quote',d) returning tc_records.id into id;update public.tc_businesses set next_quote=next_quote+1 where tc_businesses.id=b.id;
  else
   perform tc_private.need(r.kind='quote' and r.data->>'status'<>'aceptado','Duplicá el presupuesto aceptado.');perform tc_private.need(r.revision=(payload->>'revision')::integer,'Otra pestaña cambió este borrador. Copiá tus cambios y recargá antes de guardar.');
   d:=d||jsonb_build_object('number',r.data->'number','status',r.data->'status');update public.tc_records set data=d,revision=revision+1 where tc_records.id=r.id;id:=r.id;
  end if;perform tc_private.event(b.id,'Borrador guardado',id);
 elsif action='publish' then
  perform tc_private.need(r.kind='quote' and r.data->>'status'<>'aceptado','No se puede publicar.');perform tc_private.need((r.data->>'expires_on')::date>=today,'Revisá el vencimiento.');
  select * into c from public.tc_records where tc_records.id=(r.data->>'customer_id')::uuid and business_id=b.id and kind='customer';
  update public.tc_public_versions set status='sustituido' where quote_id=r.id and status='pendiente';
  select count(*)+1 into version_no from public.tc_public_versions where quote_id=r.id;
  token:=encode(extensions.gen_random_bytes(32),'hex');
  d:=r.data||jsonb_build_object('business',jsonb_build_object('name',b.data->>'name','timezone',b.data->>'timezone'),'customer',jsonb_build_object('name',c.data->>'name','address',c.data->>'address'),'version',version_no);
  insert into public.tc_records(business_id,kind,data) values(b.id,'version',d) returning tc_records.id into id;
  insert into public.tc_public_versions(token_hash,business_id,quote_id,version_id,snapshot,expires_on,timezone) values(encode(extensions.digest(token,'sha256'),'hex'),b.id,r.id,id,d,(d->>'expires_on')::date,b.data->>'timezone');
  update public.tc_records set data=data||'{"status":"pendiente"}',revision=revision+1 where tc_records.id=r.id;perform tc_private.event(b.id,'Versión publicada',r.id);return jsonb_build_object('id',id,'token',token);
 elsif action='revoke' then
  perform tc_private.need(r.kind='version','Versión inválida.');update public.tc_public_versions set status='revocado' where version_id=r.id and status='pendiente';id:=r.id;perform tc_private.event(b.id,'Enlace revocado',id);
 elsif action='convert' then
  perform tc_private.need(r.kind='quote' and r.data->>'status'='aceptado','Primero necesitás un presupuesto aceptado.');select * into j from public.tc_records where business_id=b.id and kind='job' and data->>'quote_id'=r.id::text;
  if j.id is not null then return jsonb_build_object('id',j.id);end if;
  select * into v from public.tc_public_versions where quote_id=r.id and status='aceptado';d:=jsonb_build_object('quote_id',r.id,'customer_id',v.snapshot->>'customer_id','title',v.snapshot->>'title','scope',v.snapshot->>'scope','currency',v.snapshot->>'currency','amount_minor',v.snapshot->'total_minor','status','pendiente');
  insert into public.tc_records(business_id,kind,data) values(b.id,'job',d) returning tc_records.id into id;perform tc_private.event(b.id,'Trabajo creado',id);
 elsif action='job' then
  d:=payload->'data';perform tc_private.text_value(d,'title',160);perform tc_private.text_value(d,'scope',5000);perform tc_private.text_value(d,'next_action',1000,false);
  perform tc_private.need(d->>'status' in ('pendiente','en curso','en espera','terminado','cancelado') and d->>'currency' in ('ARS','USD'),'Estado o moneda inválidos.');
  if nullif(d->>'starts_on','') is not null then perform (d->>'starts_on')::date;end if; if nullif(d->>'due_on','') is not null then perform (d->>'due_on')::date;end if;if nullif(d->>'action_on','') is not null then perform (d->>'action_on')::date;end if;
  perform tc_private.need(nullif(d->>'due_on','') is null or nullif(d->>'starts_on','') is null or (d->>'due_on')::date>=(d->>'starts_on')::date,'Revisá las fechas.');
  select * into c from public.tc_records where tc_records.id=(d->>'customer_id')::uuid and business_id=b.id and kind='customer';perform tc_private.need(c.id is not null,'Cliente inválido.');minor:=tc_private.decimal_value(d->>'amount')*100;
  if r.id is null then perform tc_private.need(c.data->>'archived_at' is null,'Elegí un cliente activo.');d:=(d-'amount'-'quote_id')||jsonb_build_object('amount_minor',minor);insert into public.tc_records(business_id,kind,data) values(b.id,'job',d) returning tc_records.id into id;
  else
   perform tc_private.need(r.kind='job' and r.revision=(payload->>'revision')::integer,'El trabajo cambió. Recargá antes de guardar.');perform tc_private.need(d->>'customer_id'=r.data->>'customer_id' and d->>'currency'=r.data->>'currency','Cliente y moneda no se modifican.');
   if minor<>(r.data->>'amount_minor')::bigint then
    perform tc_private.need(payload->>'confirm_change'='true' and length(btrim(payload->>'reason'))>=3,'Confirmá el cambio de importe e indicá el motivo.');select coalesce(sum((data->>'amount_minor')::bigint),0) into collected from public.tc_records where business_id=b.id and kind='payment' and data->>'job_id'=r.id::text and data->>'voided_at' is null;perform tc_private.need(minor>=collected,'El importe no puede ser menor a lo cobrado.');
   end if;
   d:=(d-'amount')||jsonb_build_object('amount_minor',minor,'quote_id',r.data->'quote_id');update public.tc_records set data=d,revision=revision+1 where tc_records.id=r.id;id:=r.id;
  end if;perform tc_private.event(b.id,case when r.id is null then 'Trabajo creado' else 'Trabajo guardado' end,id,jsonb_build_object('before',r.data,'after',d,'reason',payload->>'reason'));
 elsif action='pay' then
  perform tc_private.need(r.kind='job','Trabajo inválido.');d:=payload->'data';minor:=tc_private.decimal_value(d->>'amount')*100;
  perform tc_private.need((d->>'key')::uuid is not null,'Falta la clave de reintento.');select * into old from public.tc_records where business_id=b.id and kind='payment' and data->>'key'=d->>'key';
  result:=jsonb_build_object('job_id',r.id,'customer_id',r.data->>'customer_id','amount_minor',minor,'currency',d->>'currency','paid_on',d->>'paid_on','method',d->>'method','reference',coalesce(d->>'reference',''),'key',d->>'key');
  if old.id is not null then perform tc_private.need(old.data-'voided_at'-'void_reason'=result,'La clave de reintento pertenece a otro movimiento.');return jsonb_build_object('id',old.id);end if;
  select coalesce(sum((data->>'amount_minor')::bigint),0) into collected from public.tc_records where business_id=b.id and kind='payment' and data->>'job_id'=r.id::text and data->>'voided_at' is null;
  perform tc_private.need(minor>0 and minor<=(r.data->>'amount_minor')::bigint-collected and d->>'currency'=r.data->>'currency','El importe debe ser mayor a cero, no superar el saldo y usar la misma moneda.');
  perform tc_private.need((d->>'paid_on')::date<=today and d->>'method' in ('transferencia','efectivo','tarjeta','otro'),'Fecha o medio inválido.');perform tc_private.text_value(d,'reference',190,false);
  insert into public.tc_records(business_id,kind,data) values(b.id,'payment',result) returning tc_records.id into id;perform tc_private.event(b.id,'Cobro registrado',r.id);
 elsif action='void' then
  perform tc_private.need(r.kind='payment','Movimiento inválido.');perform tc_private.need(length(btrim(payload->>'reason')) between 3 and 1000,'Indicá el motivo de anulación.');
  update public.tc_records set data=data||jsonb_build_object('voided_at',coalesce(data->>'voided_at',now()::text),'void_reason',coalesce(data->>'void_reason',payload->>'reason')) where tc_records.id=r.id;id:=r.id;perform tc_private.event(b.id,'Cobro anulado',r.id);
 else perform tc_private.need(false,'Acción desconocida.');end if;
 return jsonb_build_object('id',id);
end$$;

create function public.tc_public_quote(token text,p_decision jsonb default null) returns jsonb language plpgsql security definer set search_path='' as $$
declare v public.tc_public_versions;b public.tc_businesses;
begin
 perform tc_private.need(token ~ '^[a-f0-9]{64}$','Enlace no disponible.');
 select * into v from public.tc_public_versions where token_hash=encode(extensions.digest(token,'sha256'),'hex');perform tc_private.need(v.version_id is not null,'Enlace no disponible.');
 -- Same lock order as owner commands: business first, then public version.
 select * into b from public.tc_businesses where id=v.business_id for update;
 select * into v from public.tc_public_versions where token_hash=v.token_hash for update;
 perform tc_private.need((b.expires_at is null or b.expires_at>now()) and v.status not in ('revocado','sustituido'),'Enlace no disponible.');
 if p_decision is not null then
  perform tc_private.limit_request('decision:'||v.token_hash,8,60);
  perform tc_private.need(v.status='pendiente' and v.expires_on>=(now() at time zone v.timezone)::date,'Esta versión ya fue respondida o venció.');
  perform tc_private.need(p_decision->>'decision' in ('aceptado','rechazado','modificacion') and p_decision->>'confirmed'='true','Confirmá tu decisión.');perform tc_private.text_value(p_decision,'name',120);perform tc_private.text_value(p_decision,'message',2000,false);
  update public.tc_public_versions set status=p_decision->>'decision',decision=p_decision where token_hash=v.token_hash;
  update public.tc_records set data=data||jsonb_build_object('status',p_decision->>'decision'),revision=revision+1 where id=v.quote_id;
  perform tc_private.event(b.id,'Decisión registrada',v.quote_id,jsonb_build_object('decision',p_decision->>'decision'));v.status:=p_decision->>'decision';
 end if;
 return jsonb_build_object('snapshot',v.snapshot,'status',case when v.status='pendiente' and v.expires_on<(now() at time zone v.timezone)::date then 'vencido' else v.status end);
end$$;
create function public.tc_send_feedback(screen text,description text,contact text default '',website text default '') returns void language plpgsql security definer set search_path='' as $$
begin
 perform tc_private.need(screen in ('landing','login','register','dashboard','customers','quotes','jobs','settings','public-quote','other') and length(btrim(description)) between 10 and 3000 and length(contact)<=190 and website='','Revisá el comentario y la pantalla.');
 perform tc_private.limit_request('feedback:'||coalesce(auth.uid()::text,md5(coalesce(current_setting('request.headers',true),''))),5,3600);
 insert into public.tc_feedback(screen,description,contact,release) values(screen,description,nullif(contact,''),'0.2.0-beta.1');
end$$;
create function public.tc_send_pilot(name text,email text,message text,website text default '') returns void language plpgsql security definer set search_path='' as $$
begin
 perform tc_private.need(length(btrim(name)) between 1 and 120 and length(email)<=190 and email ~ '^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$' and length(btrim(message)) between 15 and 2000 and website='','Revisá nombre, correo y mensaje.');
 perform tc_private.limit_request('pilot:'||md5(coalesce(current_setting('request.headers',true),'')),3,3600);
 insert into public.tc_pilot_requests(name,email,message) values(name,email,message);
end$$;
revoke all on function public.tc_send_pilot(text,text,text,text) from public;
grant execute on function public.tc_send_pilot(text,text,text,text) to anon,authenticated;
create function public.tc_prune() returns void language plpgsql security definer set search_path='' as $$begin
 delete from public.tc_businesses where is_demo and expires_at<now();delete from public.tc_feedback where created_at<now()-interval '90 days';delete from tc_private.limits where started_at<now()-interval '2 days';end$$;
-- No browser caller may invoke maintenance or private helpers.
revoke all on all functions in schema tc_private from public,anon,authenticated;
revoke all on function public.tc_prune() from public,anon,authenticated;
revoke all on function public.tc_command(text,jsonb), public.tc_public_quote(text,jsonb),public.tc_send_feedback(text,text,text,text) from public;
grant execute on function public.tc_command(text,jsonb) to authenticated;
grant execute on function public.tc_public_quote(text,jsonb),public.tc_send_feedback(text,text,text,text) to anon,authenticated;
commit;
