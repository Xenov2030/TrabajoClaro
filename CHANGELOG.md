# Changelog de TrabajoClaro

Primer registro documental del estado observado. **No se reconstruye una cronología anterior ni se asignan fechas de lanzamiento sin evidencia.** No hay release público o despliegue externo verificados.

El código identifica la prueba como `2026.10-beta.2` en `config/beta.php`; ese valor no se convierte aquí en una versión histórica publicada.

## Sin publicar

### Migración autorizada a Next.js y Supabase

Agregados:

- Aplicación Next.js/React para Vercel con Supabase Auth opcional Google/correo/anónimo, RPC PostgreSQL, RLS y migración SQL dedicada.
- Recorrido portado: clientes, borradores/versiones/decisiones públicas, trabajos, cobros/saldos/anulación, panel, CSV, feedback, solicitudes de piloto y eventos separados de auditoría.
- Pruebas de dinero, PostgreSQL real y Chromium móvil/escritorio; documentación de conexión, operación, estado y limitaciones.

Correcciones:

- Configuración de Vercel usa Next.js y `.next`; el build anterior detectaba Vite y esperaba `dist`.
- Refresco sin desmontar el detalle que conserva el enlace recién publicado; reconocimiento de `/p` normalizado y fechas por zona del negocio.

Cambios relevantes:

- Laravel/MySQL se conserva como referencia en `legacy/laravel` y Git; no importación automática ni borrado de bases previas. Informes anteriores archivados.
- Marca/favicons/Manrope/firma y su presencia visible en README conservados. Los enlaces compartidos nuevos usan token en fragmento para evitar enviarlo en la ruta a Vercel.
- El código migrado no acredita Supabase Auth/Google/SMTP ni despliegue público configurados; PWA sigue pendiente. No nuevas funciones comerciales ni contratos pagos.

### Cierre documental de la primera beta

- Agregado: `docs/ESTADO_BETA.md` con requisitos consolidados, evidencia local, pendientes, consulta técnica de feedback y guion de diez minutos.
- Corregido: README y guía de testers distinguen el recorrido probado de la PWA, la publicación y las funciones posteriores aún pendientes.
- Cambio relevante: alcance cerrado sin módulos nuevos; suites repetidas SQLite/MySQL (34 pruebas, 333 comprobaciones cada una) y Chromium (8 recorridos). Procedimiento de rollback documentado, sin afirmar ensayo operativo completo.


### Agregados

- README con logotipo visible que adapta su variante al tema claro/oscuro de GitHub y muestras reales del icono de aplicación y favicon.

- Identidad provisional integrada: símbolo original sobre grilla, logotipo SVG horizontal, eslogan HTML, variantes claro/oscuro/mono y muestra local `/marca`.
- Manrope alojada localmente con licencia OFL/procedencia; favicon SVG/ICO, PNG y metadatos sin promesa de modo offline.
- `docs/MARCA.md` con uso, recursos y búsqueda externa bloqueada (disponibilidad no determinada).

- Base factual para contenido en `docs/CONTENIDO.md`, con fuentes de código, hipótesis pendientes, límites de afirmaciones y atribución verificable del aporte de Codex.
- Guía de capturas en `docs/GUIA_CAPTURAS.md`, con pantallas existentes, datos ficticios y protección de información sensible.
- Este changelog, sin releases, fechas históricas ni resultados comerciales inventados.

### Correcciones documentales

- README contrastado con rutas, servicios, vistas, configuraciones y suites del checkout actual.
- Retiradas del README las credenciales de ejemplo; la prueba interactiva recomienda demo aislada o cuenta propia.
- Arranque local corregido para incluir `scheduler`, conservación de `.env` y advertencia de no regenerar una clave existente.
- Separados los comandos de PHPUnit para SQLite y MySQL; corregida la descripción de las pruebas de navegador, que también crean cuentas y feedback persistentes.
- Aclarados los prerrequisitos de la imagen de producción, la limitación de Vite bajo la CSP actual y el alcance real del script de backup (sin cifrado/subida/programación automática).
- Diferenciadas verificación local, configuración de HTTPS, publicación en el remoto y despliegue público.

### Cambios relevantes de documentación

- Tabla de funciones implementadas/parciales/pendientes; límites y roadmap sin fechas prometidas.
- Variables explicadas sin valores secretos; instrucciones de instalación existentes conservadas y corregidas.
- Indicada la falta de evidencia de entrevistas, adopción, métricas comerciales y revisión humana independiente.
- Resultados de esta revisión separados de los informes anteriores; estos últimos siguen como registro de etapas locales.

## Base funcional observada al crear este registro

Inventario del código existente, **no una afirmación de que todo se agregó en esta revisión documental**:

### Agregados presentes en el producto

- Landing beta, solicitudes de piloto, registro/ingreso/salida y negocio propietario con estructura de membresías.
- Clientes, presupuestos con cálculos exactos, versiones privadas revocables y decisiones sin cuenta del destinatario.
- Trabajos manuales o desde un presupuesto aceptado, lista/tablero, próxima acción e historial.
- Cobros manuales parciales, saldo, anulación con motivo, idempotencia, ARS/USD separados y CSV autorizado.
- Demo aislada por visitante, feedback con versión/pantalla y limpiezas programadas.
- Controles de pertenencia, encabezados/CSRF, logs filtrados, pruebas y preparación de producción con persistencia.

### Correcciones anteriores documentadas

- Directivas Blade y validación de cambios de importe; comparación del JSON persistido en MySQL.
- Límites independientes por acción, privacidad en errores de enlaces y espera de disponibilidad al arrancar.
- Navegación móvil, tarjetas en el presupuesto del cliente y controles de ítems/carga.

Estas correcciones constan en los informes y el registro de la sesión; no se les asigna una fecha ni versión histórica nueva. Fuentes: [informe inicial](docs/legacy-laravel/verificacion.md) e [informe beta 2](docs/legacy-laravel/beta-2-verificacion.md).

### Estado relevante

- No hay URL pública verificada ni proveedor/dominio conectado; el ensayo de producción es local.
- No hay procesamiento de pagos, comprobantes fiscales, recuperación de contraseña, invitaciones ni evidencia de resultados comerciales.
- Revisión documental del **2026-10-31**, zona `America/Buenos_Aires`; esta fecha describe la revisión, no el nacimiento o lanzamiento de todas las funciones.
