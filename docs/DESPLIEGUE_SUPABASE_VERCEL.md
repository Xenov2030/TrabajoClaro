# Conectar TrabajoClaro con Supabase y Vercel

La migración cambia el runtime a Next.js/React y PostgreSQL de Supabase. Google es un proveedor de acceso opcional; **los datos no van al Drive de cada usuario**. El código no necesita PHP ni MySQL en Vercel.

No se creó un proyecto, contrató un servicio ni modificó la base de Jornadapp. Usar un **proyecto Supabase nuevo dedicado a TrabajoClaro**. Revisar límites, posibilidad de pausa, cuotas y costos actuales en las consolas antes de aceptar un plan; no se promete gratuidad permanente.

## 1. Crear la base

1. En Supabase, crear el proyecto dedicado y guardar su contraseña en un gestor seguro. No pegar secretos en el chat ni en GitHub.
2. Esperar a que esté disponible. En SQL Editor, revisar y ejecutar **una sola vez** [la migración](../supabase/migrations/202611010001_trabajoclaro.sql). Es transaccional; no usar sobre una base que ya tenga estas tablas. Requiere pgcrypto en el esquema `extensions`, habitual en Supabase. Si está en otro esquema, resolverlo antes de ejecutar, sin mover extensiones de proyectos existentes.
3. Verificar tablas `tc_businesses`, `tc_records`, `tc_public_versions`, `tc_feedback`, `tc_pilot_requests` y RLS activo. Las escrituras van por RPC con autorización, no mediante permisos generales de edición. No deshabilitar RLS para solucionar un error.
4. En configuración/API del proyecto, obtener **Project URL** y **Publishable key** (o clave `anon` heredada). Nunca usar `service_role` ni `sb_secret_` en el frontend.

## 2. Configurar autenticación

1. En Authentication / URL Configuration, poner como Site URL el dominio definitivo del proyecto de Vercel.
2. Agregar la URL exacta `https://DOMINIO_REAL/auth/callback` a Redirect URLs. `DOMINIO_REAL` es un marcador; no es un sitio que se haya publicado.
3. Habilitar Email/Password para cuentas de prueba. Si está activada confirmación por correo, comprobar entrega y cuotas; configurar SMTP si hace falta. No mostrar un envío como probado sin recibirlo.
4. Para «Probar demo», habilitar **Anonymous Sign-ins**, revisar límites y protección contra abuso. Cada visitante recibe un usuario anónimo diferente. La demo no comparte una contraseña ni privilegios administrativos.
5. Opcional Google: habilitar el proveedor Google en Supabase; crear/configurar un cliente OAuth web en Google Cloud y usar **la URL de callback de Supabase que muestra su consola** como redirect autorizado. Client secret solo dentro del proveedor, nunca en `NEXT_PUBLIC_*`. No reutilizar ni modificar a ciegas la configuración de Jornadapp. En modo de pruebas de Google, revisar quién está autorizado como tester.

## 3. Configurar Vercel

1. Proyecto existente `trabajo-claro`, repositorio `Xenov2030/TrabajoClaro`, rama `main`, Root Directory raíz.
2. Framework Preset: **Next.js**. Node.js **24.x**.
3. Build Command: `npm run build`. Output Directory: valor por defecto de Next.js (`.next`). **Quitar cualquier override `dist` o `public/build`** y cualquier preset Vite. La configuración del repositorio está en [vercel.json](../vercel.json).
4. En Environment Variables, agregar para los entornos que se usarán:

   | Variable | Valor |
   | --- | --- |
   | `NEXT_PUBLIC_SUPABASE_URL` | Project URL HTTPS de Supabase |
   | `NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY` | Publishable key o anon; no una clave privilegiada |

5. Redeploy del commit que contiene la migración. Las variables `NEXT_PUBLIC_*` se incorporan durante el build: cambiarlas exige recompilar. No hace falta copiar el `.env` de Laravel, `APP_KEY`, `DB_PASSWORD` ni una contraseña de base al frontend.
6. Revisar logs de build. Next.js debe generar sus rutas; no basta con que Vite produzca CSS. Si el preset sigue buscando `dist`, revisar los overrides del panel.

## 4. Limpieza, respaldo y recuperación

- La demo expira a las 24 h en autorización y RLS aunque el cron esté detenido. Los datos se eliminan al ejecutar `public.tc_prune()`; sin programación, no prometer eliminación física a las 24 h.
- Activar Cron/pg_cron desde la consola del proyecto, si el plan lo permite. Como operador, ejecutar una vez:

  ```sql
  select cron.schedule('trabajoclaro-cleanup', '0 * * * *', 'select public.tc_prune()');
  ```

- Revisar `cron.job` y `cron.job_run_details`; no repetir la programación creando múltiples jobs. La función no puede ejecutarse desde un navegador anónimo o autenticado. Limpia demos vencidas, feedback de más de 90 días y contadores antiguos. No elimina cuentas registradas por inactividad; los usuarios anónimos de Auth requieren mantenimiento separado autorizado. No hay worker de colas.
- Configurar backups del proyecto según plan y exportación segura con las herramientas de Supabase/PostgreSQL. **No hay respaldo externo automático configurado por el código.** No asumir que el plan gratuito incluye recuperación puntual. Guardar dumps cifrados fuera de Git, `public/` y logs.
- Restaurar primero en un proyecto/base de prueba; preservar también configuración de Auth y usuarios de forma soportada por Supabase. Verificar propietarios/RLS, documentos/decisiones y cobros. Un CSV no restaura Auth ni toda la base.
- La migración y el flujo se probaron en PostgreSQL efímero; no se declara ensayada una restauración completa del proveedor. El ensayo anterior de MySQL no acredita recuperación de Supabase.
- Rollback: detener escrituras antes de restaurar datos. Volver a un deployment Next.js compatible no revierte la base. No borrar tablas ni aplicar SQL inverso a ciegas. Volver a Laravel requiere su hosting y base MySQL anteriores; **Vercel no ejecutará Laravel por seleccionar un commit viejo**. Copias y volúmenes MySQL existentes no se eliminaron ni importaron automáticamente.

## 5. Comprobación pública obligatoria

URL proporcionada por el titular: `https://trabajo-claro-git-main-xenov.vercel.app/`. Se observó en sus capturas un deployment anterior fallido (`STATIC_BUILD_NO_OUT_DIR`). La red de desarrollo bloqueó el acceso a ese dominio (`CONNECT 403` del proxy, sin llegar al sitio). **No se acredita que esta URL ya ejecute la migración.**

Después del redeploy, verificar desde navegador real y otra red:

1. Landing, marca/favicon, versión `0.2.0-beta.1`; `/up` devuelve estado sin datos. No comprueba conexión de DB.
2. Demo habilitada, cliente/presupuesto propios; nueva sesión privada no ve los anteriores.
3. Registro, correo si aplica, inicio/cierre y reingreso, Google si se configuró, configuración del negocio.
4. Presupuesto ARS 1.000,00 → publicar → enlace en otra sesión → aceptación → trabajo → cobro ARS 250,00 → saldo ARS 750,00. La aceptación no es firma certificada, el cobro es manual.
5. Feedback guardado y consultable solo por el operador; CSV del propio negocio.
6. Dos propietarios: no leer ni mutar IDs ajenos; persistencia tras nuevo deployment y cierre/reingreso.
7. Cron, logs sin secretos, backup y restauración del proveedor. Confirmar suspensión/cuotas/vigencia reales.

Presupuestos nuevos usan `/p/#TOKEN`: el fragmento no se envía como ruta al servidor de Vercel. El token se transmite a Supabase en el cuerpo RPC, nunca en una consulta de selección pública. No registrar cuerpos de solicitudes ni enlaces privados en analítica; revisar configuración del proveedor. No hay service worker ni caché de documentos privados, modo offline o instalación PWA terminada.
