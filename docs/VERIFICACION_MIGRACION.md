# Verificación de la migración

Revisión: 2026-10-31 (Argentina). Código `0.2.0-beta.1`, Next.js 16.4.3, React 19 y Node 24.19.0 según instalación/lock. Sin URL pública de la migración acreditada. La autorización de cambio de stack no incluye modificar Jornadapp ni contratar servicios.

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

## Pendiente, sin equivalencias falsas

No se verificaron registro/login/logout reales, OAuth Google, entrega de correo, alta anónima alojada, permisos del proyecto Supabase real, deployment Vercel actualizado, cuotas/suspensión, persistencia tras redeploy del proveedor, cron alojado, backup/restauración de Supabase, instalación PWA/standalone, Safari/Firefox, dispositivos físicos, carga concurrente, auditoría independiente o recorrido completo solo con teclado. Las pruebas Laravel y su restauración MySQL quedan históricas y no sustituyen estas comprobaciones.

La conexión Vercel/GitHub está informada por el titular y las capturas del build anterior, pero el acceso desde esta tarea fue bloqueado por el proxy (`CONNECT 403`, HTTP de origen no obtenido). No se inventó una URL alternativa ni se cambió la salida a assets estáticos para aparentar un backend desplegado.
