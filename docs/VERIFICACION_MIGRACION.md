# Verificación de la migración

Última revisión: 2026-11-01 (Argentina). Código `0.2.0-beta.1`, Next.js 16.4.3, React 19 y Node 24.19.0 según instalación/lock. URL pública y recorrido de demo verificados el 2026-11-01; ver el alcance más abajo. La autorización de cambio de stack no incluye modificar Jornadapp ni contratar servicios.

## Resultados locales

| Verificación | Resultado y alcance |
| --- | --- |
| `npm run build` | Build Next.js de producción aprobado; rutas Next.js y `/up`. Configuración Vercel `.next`, sin PHP runtime. |
| `npm test` | **5 pruebas aprobadas**: redondeo exacto, descuento/impuesto, entradas inválidas, límite y CSV/formato. BigInt en cliente, no float monetario. |
| `bash scripts/test-database.sh` | **Aprobado** contra PostgreSQL 17 efímero. Aplica la migración desde cero con roles anon/authenticated. Assertions de autorización, dinero y recorrido, sin servicios alojados. |
| `npm run test:e2e` | **4 pruebas aprobadas**, Chromium 360×800 y 1440×1000, build de producción sin Supabase configurado: rutas públicas, marca, firma, salud, ausencia de desbordes, error explícito y campos de feedback conservados. |
| `bash scripts/test-integration.sh` | **2 recorridos aprobados**, Chromium móvil/escritorio contra PostgreSQL/PostgREST reales. Sesión JWT preemitida de prueba; no Supabase Auth alojado. |
| PDF | Dos PDF de navegador (móvil/escritorio) con una sola firma exacta y anotación de enlace al portfolio. |
| Identidad y diseño | Capturas de landing/panel/editor/vista pública/trabajo; marca conservada y tablas reemplazadas por ítems adaptables. Carga de activos y ausencia de desbordes comprobadas en rutas públicas y trabajo. |
| `npm audit --omit=dev` | Cero vulnerabilidades conocidas reportadas en dependencias de producción en esta ejecución; no certificación de seguridad. |

La prueba SQL conserva: negocio propio, RLS de lectura, escritura directa denegada, IDs ajenos rechazados, referencia de cliente propio, total 1.500,50, cantidades/precios inválidos, conflicto por revisión, hash/token de 64 caracteres, sustitución/revocación/vencimiento, snapshot inmutable, confirmación/decisión única, conversión idempotente, pago parcial, reintento idéntico y discrepante, rechazo de sobrepago/moneda distinta, anulación con motivo, feedback/piloto sin lectura general, métricas iniciales/demos excluidas y limpieza de demo con documentos. Las transacciones de prueba se revierten y sus contenedores son efímeros.

Los dos recorridos de integración crean cliente/presupuesto con dos ítems, publican, abren el enlace en un contexto independiente sin sesión, aceptan, convierten, registran ARS 500 y comprueban saldo ARS 1.000,50, envían feedback y solicitud de piloto. Las respuestas del dominio operativo son **PostgREST/PostgreSQL reales**; no se interceptan con una maqueta. El arnés reemplaza únicamente el proveedor externo de identidad por una sesión preemitida y verifica su firma en PostgREST. No ofrece login/Google/correo simulados como exitosos.

## Reproducción

```sh
npm ci
npm test
npm run build
npm run test:e2e
bash scripts/test-database.sh
bash scripts/test-integration.sh
```

Navegador: por defecto `/usr/bin/chromium`; para el Chromium descargado de Playwright usar `PLAYWRIGHT_BUNDLED=1`. Hace falta instalarlo, por ejemplo `npx playwright install chromium`. Los scripts de DB necesitan Docker y descargan PostgreSQL/PostgREST si faltan. No exponen el puerto de PostgreSQL; la API de prueba solo usa loopback. Requieren libres 3000 para pruebas públicas y 3001/54320/54321 para integración. **Nunca ejecutar el arnés contra un proyecto real ni copiar `tests/database/bootstrap.sql` a Supabase.**

Capturas/PDF quedan en `test-results/public` y `test-results/integration`, ignorados en Git. Los JWT, usuario/secret de prueba solo sirven a los contenedores efímeros, no son credenciales de testers o producción. CI contiene las mismas comprobaciones; su ejecución remota no se declara aprobada hasta observar GitHub Actions.

## Errores encontrados y corregidos

- La actualización de datos desmontaba el detalle y perdía el enlace recién publicado: se conserva el componente durante el refresco en segundo plano.
- La ruta pública no reconocía `/p` tras normalizar la barra final de Next.js: acepta `/p` y lee el token del fragmento.
- El arnés local necesitó admitir los headers de Supabase y el formato `request.jwt.claims` de PostgREST. No se modificó producción para eludir autorización.
- Una etiqueta exacta de prueba omitía el asterisco accesible de un campo requerido; se corrigió el selector y se repitió el recorrido real.
- Fechas de cobro se prueban con la zona del negocio; usar `current_date` UTC en el ensayo podía representar mañana en Argentina.

## Verificación pública · 2026-11-01

**Dominio exacto:** https://trabajo-claro-orpin.vercel.app/ · versión visible `0.2.0-beta.1`. Pruebas contra Vercel y Supabase reales, sin sesiones preemitidas ni respuestas simuladas. La habilitación de acceso anónimo fue realizada por el titular en Supabase; las pruebas no modificaron configuración ni código.

| Comprobación pública | Resultado observado |
| --- | --- |
| Acceso HTTPS y salud | Landing HTTP 200 sin login de Vercel; `/up` HTTP 200 con estado y versión. No certifica salud de DB/cron. |
| Pantallas y recursos | 12 comprobaciones de rutas (landing, marca, acceso, registro, feedback y beta × móvil/escritorio), sin desbordes horizontales ni errores JS detectados. SVG/ICO/PNG/Apple/manifest respondieron HTTP 200 con tipos adecuados; firma única, destino y foco comprobados. |
| Demo real | Alta anónima en Supabase y panel del negocio aislado, sin cuenta compartida. |
| Recorrido móvil | Chromium 360×800: crear cliente y presupuesto con dos ítems, total ARS 1.500,50; publicar; abrir en contexto independiente, aceptar y guardar PDF; convertir a trabajo, registrar ARS 500 y comprobar saldo ARS 1.000,50; guardar feedback y solicitud de piloto. Aprobado. |
| Recorrido escritorio | Chromium 1440×1000: mismo recorrido con otra demo y datos ficticios. Aprobado. |
| PDFs públicos | Dos archivos (móvil/escritorio), una aparición de la firma exacta y anotación del enlace al portfolio. No prueba de todos los visores PDF. |
| Aislamiento de lectura | Demo A crea y lee su cliente; consulta directa por el mismo ID desde B devuelve lista vacía por RLS. |
| Aislamiento de mutación | RPC de B para archivar el ID de A rechazada (HTTP 400, registro no encontrado en su negocio); A conserva sus datos intactos. Solo IDs de demos creadas por esta prueba. |
| Persistencia de sesión | Recargar conserva acceso al panel y negocio. No es un ensayo de reinicio/redeploy o recuperación del proveedor. |
| Cierre de sesión | Logout real; volver a `/app` solicita ingreso y no muestra datos privados. |

Se crearon exclusivamente usuarios anónimos/demos y documentos ficticios; feedback y solicitudes de piloto se identifican como pruebas técnicas. No se borraron registros ajenos ni se enviaron WhatsApp/correos. Los datos de demo quedan sujetos a la retención y al cron configurado; no se afirma que ya se hayan eliminado. Las solicitudes/feedback de prueba no son adopción ni métricas comerciales.

Evidencia de esta sesión: reportes temporales `/tmp/tc-public-flow-results/report.json` y `/tmp/tc-live-isolation-report.json`; capturas y PDFs en `/tmp/tc-public-flow-results/`. Son artefactos temporales, no enlaces públicos permanentes. Este informe conserva los resultados en Git sin subir claves, JWT, IDs de cuentas, tokens de documentos ni capturas de configuración.

Incidencias del despliegue identificadas: el enlace de rama redirigía al login de Vercel; un build anterior no incorporaba Supabase y el siguiente rechazó una Project URL con `/rest/v1/`. El titular corrigió la URL base y redesplegó. Después, Supabase devolvió `anonymous_provider_disabled`; al habilitarlo el titular, el recorrido público pasó. El error `CONNECT 403` pertenecía al entorno anterior y ya no bloqueó el dominio público durante esta verificación.

## Pendiente, sin equivalencias falsas

No se verificaron registro/login reales con correo y contraseña, confirmación/entrega de correo, OAuth Google, cuotas/suspensión/disponibilidad continua, persistencia tras reinicio o redeploy del proveedor, cron alojado, backup/restauración de Supabase, instalación PWA/standalone, Safari/Firefox, dispositivos físicos, carga concurrente, auditoría independiente o recorrido completo solo con teclado. Las pruebas Laravel y su restauración MySQL quedan históricas y no sustituyen esas comprobaciones. Ese estado de PWA fue reemplazado por la implementación posterior descrita en [PWA](PWA.md): pruebas de interfaz/criterios y privacidad realizadas; instalación nativa/standalone real aún pendiente.

## Diagnóstico de Google · 2026-11-01

Se pulsó «Continuar con Google» desde `/login` en el dominio público, sin ingresar una cuenta Google. Supabase respondió HTTP 400 en `/auth/v1/authorize`, con `Unsupported provider: provider is not enabled`. Esto confirma que el botón inicia el flujo, pero el proveedor sigue deshabilitado. [Configuración y prueba manual](GOOGLE.md). No se afirma que funcionen consentimiento, retorno o acceso real.

## Instalación PWA · 2026-11-01

Se implementaron controles nativos condicionados por disponibilidad, instrucciones `/instalar`, manifest con identidad estable/standalone, maskable y worker de red sin caché privada/offline. Las pruebas emulan eventos para verificar interacción, no certifican instalación real. Un perfil persistente de Chromium devolvió cero errores de instalabilidad, pero `PWA.install` falló con `kWebAppDisabled`; no se comprobó apertura standalone ni login/logout desde una aplicación instalada. El callback ahora redirige automáticamente al negocio al recibir sesión; prueba local con DB y sesión preemitida, no un ingreso Google independiente. [Detalle y guion manual](PWA.md).
