<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="public/brand/logo-horizontal-dark.svg">
    <img src="public/brand/logo-horizontal.svg" alt="TrabajoClaro: símbolo y nombre del producto" width="316" height="64">
  </picture>
</p>

# TrabajoClaro

**Cada trabajo en orden. Cada cobro, claro.**

Aplicación para pequeños negocios de servicios y fabricación por encargo: cliente → presupuesto → aprobación → trabajo → cobro. Busca reunir alcance acordado, próximas acciones y saldos. La utilidad y disposición a pagar son hipótesis pendientes de validar; no hay métricas comerciales, usuarios ni testimonios acreditados.

**Autor:** [Nicolas Aguirres](https://nicolasaguirrescode.com/). **Estado:** beta pública con recorrido principal verificado en Next.js + Supabase, identificador `0.2.0-beta.1`; no versión estable ni PWA instalada verificada. Última revisión: 2026-11-01 (Argentina).

La versión Laravel/MySQL se conserva en [legacy/laravel](legacy/laravel) y en el historial Git; las pruebas y procedimientos anteriores están en [docs/legacy-laravel](docs/legacy-laravel). **Sus resultados no acreditan esta nueva arquitectura.** No se eliminaron ni importaron automáticamente las bases/volúmenes MySQL existentes.

## Publicación y configuración

**URL pública verificada:** [https://trabajo-claro-orpin.vercel.app/](https://trabajo-claro-orpin.vercel.app/). El 2026-11-01 se probaron en Chromium móvil y escritorio demo, cliente, presupuesto, publicación, aceptación independiente, trabajo, cobro parcial/saldo, PDF, feedback y solicitud de piloto. También se comprobaron aislamiento de lectura/mutación entre dos demos, recarga y logout. [Evidencia y límites](docs/VERIFICACION_MIGRACION.md#verificación-pública--2026-11-01).

El enlace de rama `trabajo-claro-git-main-xenov.vercel.app` redirigió al login de Vercel; usar el dominio público anterior para testers. La disponibilidad continua, cuotas y suspensión del proveedor siguen sin comprobarse.

**Paso a paso:** [conectar Supabase y Vercel](docs/DESPLIEGUE_SUPABASE_VERCEL.md). Requiere un proyecto Supabase dedicado, ejecutar SQL, configurar autenticación y dos variables públicas de build. Google permite iniciar sesión; **la base PostgreSQL de Supabase guarda los datos**, no el Drive de cada persona.

La aplicación no usa claves privilegiadas de Supabase. Sin configuración muestra errores explícitos; no guarda una maqueta ni simula una demo exitosa. Revisar planes/cuotas/pausas/costos actuales antes de activar servicios. No se contrataron servicios ni se modificó Jornadapp.

## Funciones y límites reales

| Función | Estado de la migración |
| --- | --- |
| Landing, identidad, logo/favicons, acceso, registro, feedback y beta | Pantallas y recursos comprobados también en el dominio público; esto no acredita registro por correo completo. |
| Ingreso y cuentas | Alta anónima alojada y logout verificados públicamente. **Registro/login con correo, confirmación y Google siguen pendientes de comprobación.** |
| Negocio propio y clientes | RPC/RLS por propietario, edición y archivo sin borrar historia; pruebas reales de PostgreSQL. |
| Presupuesto y totales | Borrador manual, ítems, moneda, descuento, impuesto, anticipo, alcance/condiciones; cálculo exacto PostgreSQL y prevalidación BigInt. |
| Versiones y decisión pública | Snapshot, token con hash, revocación/sustitución/vencimiento, nombre y confirmación. Lectura no decide. RPC probado con PostgreSQL. |
| Trabajos | Conversión idempotente, alta/edición manual, estados, fechas, próxima acción, lista/tablero e historial. |
| Cobros | Registro manual, pagos parciales, idempotencia por clave, saldo, sobrepago rechazado y anulación con motivo. **No procesa dinero ni emite comprobantes fiscales.** |
| Aislamiento | RLS en lecturas y autorización en RPC, sin escritura directa del navegador. Pruebas de dos negocios y ataques por ID. No auditoría externa. |
| Protección de edición | Formularios conservan campos ante errores; advertencia al salir; revisión esperada en presupuesto/trabajo. Sin autosave. Creaciones generales no tienen idempotencia universal. |
| Búsqueda y organización | Búsqueda/estado/fechas y paginación de la colección cargada. Límite actual 1.000 registros; no paginación remota ni conservación al volver al detalle. |
| CSV | Exportación de clientes/presupuestos/trabajos/cobros del negocio, con datos JSON y protección de fórmulas. No mismo esquema del exportador Laravel ni importador/restaurador. |
| PDF | Impresión/guardar PDF del navegador; firma del autor separada, con enlace. No envío automático. |
| WhatsApp | Abrir mensaje/enlace para que el usuario elija contacto y decida enviar. No acredita entrega/lectura; selección directa por teléfono pendiente de paridad con Laravel. |
| Feedback | RPC de pantalla/descripción/contacto opcional/versión; sin adjuntos ni bandeja administrativa. No captura completa de formularios. |
| Solicitud de piloto y eventos mínimos de producto | Portados: almacenamiento de solicitudes sin correo automático; eventos separados de auditoría y demos excluidas. SQL probado, sin métricas comerciales acreditadas. |
| Perfil/contraseña/recuperación/baja/guía retomable | Pendientes. Configuración edita el negocio, no perfil personal. |
| PWA | **Pendiente**. Iconos/manifest existentes con `display: browser`; no instalación standalone, maskable, service worker ni offline. |

Detalle de requisitos y evidencia: [ESTADO_BETA](docs/ESTADO_BETA.md). IA, facturación fiscal, suscripciones, pagos integrados, inventario, contabilidad, nómina, marketplace, chat y apps nativas siguen fuera de la primera beta.

## Instalación local

Requiere **Node.js 24.x y npm**. Next.js/React/Supabase JS se fijan en `package-lock.json`. No requiere PHP para esta versión.

```sh
npm ci
cp .env.example .env.local
# Completar solo la URL y la clave pública de tu proyecto Supabase dedicado.
npm run dev
```

El servidor de desarrollo usa puerto 3000. Es exclusivamente local, no un acceso para testers externos. No sobrescribir un `.env.local` que ya tenga configuración válida. El `.env` de Laravel, si existe, no se usa para conectar Supabase.

Variables (también en Vercel, con redeploy después de cambiarlas):

| Variable | Explicación |
| --- | --- |
| `NEXT_PUBLIC_SUPABASE_URL` | Project URL HTTPS `https://<referencia>.supabase.co`; nunca una contraseña de base. |
| `NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY` | Publishable key o `anon`. Es pública por diseño; RLS/RPC protegen los datos. **Nunca** `service_role` ni `sb_secret_`. El build rechaza esos formatos privilegiados. |

Para crear tablas/reglas, revisar y ejecutar una vez [la migración SQL](supabase/migrations/202611010001_trabajoclaro.sql) en SQL Editor del proyecto nuevo. No usar `tests/database/bootstrap.sql` en Supabase: **es solo el reemplazo local de Auth para pruebas aisladas**. La demo crea su cliente ficticio; no hay credenciales compartidas ni seeder de producción.

```sh
npm test
npm run build
npm run start
# Con Chromium instalado; puerto 3000 libre o servidor de esta versión:
npm run test:e2e
# Con Docker para PostgreSQL efímero:
bash scripts/test-database.sh
```

El arnés adicional `playwright.database.config.js` usa PostgreSQL/PostgREST reales y sesión JWT preemitida de prueba; ver [verificación](docs/VERIFICACION_MIGRACION.md). No confundirlo con una prueba de Supabase Auth o Google reales.

## Testers y datos

El recorrido principal de demo ya se comprobó en el dominio público. Invitar inicialmente a pruebas acompañadas con datos ficticios; no presentar los pendientes como disponibles. [Guion de diez minutos](docs/TESTERS.md): demo o cuenta propia → cliente ficticio → presupuesto → publicación → aceptación en otra sesión → trabajo → cobro parcial → saldo → feedback.

- Requiere conexión. Sin trabajo offline ni sincronización local prometida.
- Demo: expira a las 24 h; limpieza física requiere cron. Usuarios anónimos de Auth requieren mantenimiento separado.
- Feedback: 90 días más siguiente limpieza; cuentas registradas sin borrado automático. Avisar al menos 7 días antes de limpieza/cierre y permitir exportar; aún no hay envío de avisos automático.
- Baja de cuenta y tratamiento de backups requieren procedimiento operativo pendiente. No prometer eliminación inmediata ni cumplimiento normativo automático.
- No hay administrador web compartido. Consulta restringida de feedback y errores en [operación](docs/OPERACION.md).
- Cuotas, suspensión y vigencia dependen de los planes reales; no verificadas.

## Identidad y atribución

<p><img src="public/icon-192.png" alt="Icono de TrabajoClaro" width="96" height="96"> <img src="public/favicon-32x32.png" alt="Favicon de TrabajoClaro" width="32" height="32"></p>

Nombre provisional, símbolo original de marco abierto/trazo escalonado, SVG claro/oscuro/mono, eslogan HTML y Manrope local con licencia SIL OFL. Recursos en [public/brand](public/brand), [fuente/licencia](public/fonts/manrope) y muestra `/marca`. [Guía de marca](docs/MARCA.md). La comprobación externa del nombre fue bloqueada; no se afirma disponibilidad legal ni se registraron dominios/marcas.

El componente [Author](src/components/brand.js) conserva **DESARROLLADO POR NICOLAS AGUIRRES**, enlace al portfolio, nueva pestaña y `rel="noopener noreferrer"`, foco visible y presencia en impresión. No se agrega a CSV ni archivos técnicos. El logo personal no reemplaza al producto.

## Estructura y mantenimiento

```text
src/app/                 Next.js, estilos y salud /up
src/components/          Pantallas, formularios y marca
src/lib/                 Cliente Supabase, dinero exacto y CSV
supabase/migrations/     Tablas, RLS, funciones transaccionales
public/                  Logo, favicon, iconos, Manrope y manifest
scripts/                 Arranque y verificación local
tests/                   Unitarias, navegador y base real
legacy/laravel/          Implementación anterior conservada
docs/                    Estado actual, despliegue y guion de prueba
```

Despliegue, cron, respaldo/restauración y límites de rollback: [guía](docs/DESPLIEGUE_SUPABASE_VERCEL.md). No se ejecutan migraciones ni borrados de datos durante el build de Vercel. La publicación del código en GitHub por sí sola no acredita deploy exitoso; el recorrido público indicado sí fue comprobado por separado.

## Evolución y base editorial

Primero configurar/probar el destino real y corregir regresiones; después completar paridad pendiente y protección de cuentas. Sin fechas prometidas. Contenido factual y capturas: [CONTENIDO](docs/CONTENIDO.md) y [GUIA_CAPTURAS](docs/GUIA_CAPTURAS.md); no hay publicaciones generadas. Los informes Laravel quedan como históricos, no como evidencia de esta versión. [CHANGELOG](CHANGELOG.md).
