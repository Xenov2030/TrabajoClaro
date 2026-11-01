# Informe de implementación y verificación

Verificación local: 31 de octubre de 2026. Sin despliegue, publicación externa, commit ni push.

## Entregado

- Monolito Laravel 12.70.2, PHP 8.4.27, MySQL 8.4.13, Blade, Tailwind y Vite; lockfiles de Composer y npm.
- Landing beta con demo previa al registro, solicitud de piloto almacenada y atribución a Nicolas Aguirres.
- Registro/login/logout, negocio propietario y estructura de membresías con autorización por negocio.
- Clientes con búsqueda, edición, archivo e historial asociado.
- Presupuestos con borrador, cálculo exacto, versiones congeladas, duplicación, impresión/PDF y enlaces privados revocables.
- Decisiones del visitante con nombre y confirmación sobre una versión exacta, sin cuenta ni firma certificada.
- Conversión idempotente a trabajo, trabajos manuales, lista/tablero, fechas, próxima acción e historial de cambios.
- Cobros parciales, claves de reintento, rechazo de sobrepagos, anulación con motivo, saldos y monedas separadas.
- Panel de atención real, WhatsApp con mensaje editable, demo por visitante y CSV seguro por negocio.
- Auditoría separada de métricas de producto, con exclusión de demos. Sin analítica externa.
- Migraciones, seeder repetible, CI configurada y documentación de instalación, operación, alcance, demo y validación comercial.

## Pruebas ejecutadas

| Verificación | Resultado |
| --- | --- |
| Suite SQLite en memoria, `php artisan test` | **27 pruebas aprobadas, 262 comprobaciones** |
| Misma suite en MySQL 8.4, `phpunit -c phpunit.mysql.xml` | **27 pruebas aprobadas, 262 comprobaciones** |
| Chromium móvil 360×800 y escritorio 1440×1000 | **2 recorridos aprobados**, sin errores JS ni desborde horizontal en landing/panel/editor/documento |
| Chromium escritorio con salida PDF real | **1 recorrido adicional aprobado**, PDF válido y formulario oculto en impresión |
| Texto del PDF con `pdftotext` | Alcance y total correctos; sin formulario de decisión |
| `npm ci` y `npm run build` | Instalación reproducible y compilación aprobadas |
| `composer validate --strict` y reinstalación desde lockfile | Aprobadas; sin clases ambiguas en el autoload final |
| `vendor/bin/pint --test` | Aprobado, 50 archivos PHP |
| `scripts/cloud-install.sh` | Ejecutado sobre la instalación existente; conserva configuración y no requiere migraciones nuevas |
| `scripts/cloud-start.sh` | Ejecutado; PHP y MySQL disponibles, respuesta HTTP con contenido esperado |
| `php artisan db:seed` por segunda vez | Aprobado; no duplica ni sobrescribe la cuenta existente |
| Logs del servidor local después del recorrido público | 0 tokens en claro; accesos `/p/` ocultados |
| `git diff --check` | Aprobado |

La suite cubre aislamiento entre dos negocios por identificadores, acciones, listas, exportaciones y claves foráneas; dinero/porcentajes/redondeos/límites; preservación de datos publicados; decisiones inválidas, duplicadas, vencidas o revocadas; conversión repetida; pagos parciales/reintentos/anulaciones/saldo; separación de monedas; CSV contra fórmulas; XSS; CSRF; límites por acción; encabezados de privacidad incluso en errores; demo aislada y limpieza; solicitud de piloto; y renderizado de las pantallas.

Además del primer recorrido, una prueba registra **dos negocios independientes por HTTP** y ejecuta para cada uno: registro → cliente → presupuesto → publicación → visitante acepta → login → trabajo → cobro parcial → saldo. Verifica 750,00 ARS y 900,00 ARS de saldo respectivamente y rechaza el acceso cruzado.

Se revisaron visualmente las ocho capturas de landing, panel, editor y presupuesto público en móvil/escritorio. Están en `test-results/` (ignorado). El PDF de la revisión adicional quedó en `/tmp/trabajoclaro-print-check/`; es una salida de prueba ficticia, no un documento fiscal.

## Problemas encontrados y corregidos

- PHP no estaba instalado y el usuario no podía instalar paquetes de sistema: se construyó el runtime Docker con extensiones requeridas y MySQL local.
- La API de GitHub no estaba permitida: Composer instaló las referencias exactas desde Git HTTPS mediante el proxy existente, con TLS activo.
- Se corrigió un cierre contiguo de directivas Blade y la validación de confirmación de importe, que inicialmente se exigía aun sin cambio.
- MySQL ordena las claves de JSON: la prueba de inmutabilidad compara el documento persistido antes y después, conservando una comparación estricta de sus valores.
- Los límites numéricos de Laravel compartían una clave entre rutas: ahora cada acción tiene un prefijo independiente y una prueba de regresión.
- Se ajustó la espera de disponibilidad para reintentar conexiones reiniciadas durante el arranque y se protegieron también las respuestas de error de enlaces privados.
- Se corrigieron detalles visuales observados y se deshabilita quitar el último ítem en lugar de ofrecer una acción sin efecto.

No quedan fallos conocidos en las verificaciones ejecutadas. Esto no equivale a una auditoría de seguridad independiente ni a una validación de mercado.

## Límites y verificaciones pendientes

- No se implementó recuperación de contraseña, correos transaccionales, invitaciones ni pagos. La interfaz lo declara; no se simulan entregas.
- Políticas legales/privacidad, retención, atención de cuentas y condiciones del piloto requieren revisión antes del lanzamiento público.
- No se ejecutó CI en GitHub ni pruebas en Firefox, Safari, dispositivos físicos o un hosting externo.
- No se enviaron mensajes WhatsApp ni correos y no se ensayó restaurar un backup real. Los procedimientos están documentados en README.
- No se realizaron pruebas de carga ni carreras entre procesos concurrentes. Se verificaron reintentos, índices únicos, transacciones y bloqueos en MySQL; antes de un uso con alta concurrencia conviene ensayar carga real.
- No se verificó restauración de este entorno en una tarea nueva. `install_script` y `start_skill` están guardados como borrador; guardar no los ejecuta ni publica el entorno.

## Arranque y próximos pasos

En este entorno preparado: `bash scripts/cloud-start.sh` desde `/workspace/TrabajoClaro`. Para refrescar dependencias: `bash scripts/cloud-install.sh`. Para una máquina propia, seguir README; se necesitan PHP 8.4.1+ (o Docker), MySQL y Node para compilar assets.

Aplicación y MySQL quedaron corriendo localmente. Los archivos de entorno, dependencias, cachés y capturas están ignorados por Git. No se modificó ninguna aplicación preexistente: el repositorio solo contenía su README.

Para reutilizar la configuración de nube, revisar y guardar el borrador en Configuración del entorno y luego publicar **el entorno** desde la interfaz. Esta acción crea el entorno reutilizable; no despliega el producto en un hosting.

Para desplegar el producto cuando se solicite: preparar HTTPS/PHP-FPM/MySQL, secretos y backups; servir `public/`; instalar dependencias y assets; migrar; configurar cron, cookies seguras y supresión de logs de tokens; comprobar restauración y políticas legales. No se ejecutó ninguno de esos pasos sobre un servidor externo.
