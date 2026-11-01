insert into auth.users(id) values('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa') on conflict do nothing;
select set_config('request.jwt.claim.sub','aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',false);
select public.tc_command('setup','{"name":"Taller de prueba","industry":"Fabricación","country":"AR","timezone":"America/Argentina/Buenos_Aires","currency":"ARS"}');
