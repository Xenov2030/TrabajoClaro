# TrabajoClaro

**Cada trabajo en orden. Cada cobro, claro.**

TrabajoClaro es una aplicación web para seguir encargos de pequeños negocios de servicios y fabricación a medida: instaladores, técnicos, carpinterías, herrerías y cartelerías.

Busca reunir el alcance acordado, la próxima acción y el saldo de cada trabajo. La necesidad y la disposición a pagar son **hipótesis pendientes de validar con usuarios**; no hay entrevistas, adopción ni resultados comerciales acreditados en esta documentación.

**Recorrido:** cliente → presupuesto → aprobación → trabajo → cobro.

- **Estado:** beta funcional para pruebas acompañadas; no versión estable ni servicio público desplegado.
- **Identificador configurado:** `2026.10-beta.2`, en [config/beta.php](config/beta.php). Puede cambiarse mediante `APP_RELEASE`; no acredita un release publicado.
- **Autor:** Nicolas Aguirres. [Portfolio](https://nicolasaguirrescode.com/).
- **Última revisión documental:** 2026-10-31, zona `America/Buenos_Aires`.
- **Cambios comprobables:** [CHANGELOG.md](CHANGELOG.md).

Esta revisión describe el código y la documentación del repositorio. El identificador de beta no acredita una versión etiquetada ni un despliegue público. La publicación en GitHub conserva el código; no equivale a publicar la aplicación para testers.

## Cierre de la primera beta

El [estado consolidado y verificable](docs/ESTADO_BETA.md) es la referencia para esta entrega: incluye todos los pedidos, evidencia, pendientes, soporte y un guion exacto de diez minutos. El recorrido principal está probado localmente; **no hay URL pública ni instalación PWA verificada**. No se incorporaron funciones nuevas en el cierre.

Quedan pendientes la guía retomable, autosave/advertencia al salir/conflictos de edición, filtros completos, bandeja administrativa de feedback, perfil/contraseña/recuperación y solicitud de baja. El manifest actual usa `display: browser`; no hay `/instalar`, diálogo nativo ni icono maskable. No presentar estos puntos como terminados ni ofrecer uso offline.

## Funciones y estado real

**Implementado** significa disponible en el código y comprobado localmente dentro de la beta. **Parcial** identifica una base existente con límites explícitos. **Pendiente** no se presenta como una función disponible.

| Función | Estado | Alcance comprobable |
| --- | --- | --- |
| Identidad de marca | Implementado, provisional | SVG originales, variantes claro/oscuro/mono, eslogan HTML, Manrope local con OFL e iconos reales. La búsqueda externa de nombre/dominios no pudo completarse. |
| Landing y solicitud de piloto | Implementado | Presentación, demo, registro y formulario que almacena solicitudes. Sin CRM ni respuesta por correo automática. |
| Registro, ingreso y salida | Implementado | Cada registro crea su negocio y membresía propietaria; configuración de rubro, país, zona horaria y moneda. |
| Clientes | Implementado | Crear, editar, buscar y archivar sin borrar documentos históricos; vista de presupuestos, trabajos y movimientos asociados. |
| Presupuestos | Implementado | Número por negocio, borrador, alcance/exclusiones, ítems, fechas, descuento fijo, impuesto porcentual, anticipo solicitado y condiciones. Duplicación disponible. |
| Cálculos | Implementado | Servidor con BCMath y centavos enteros; cantidades de hasta 3 decimales, importes de hasta 2. Redondeo por ítem y del impuesto: mitad hacia arriba. |
| Versiones compartibles | Implementado | Copia de datos al publicar; enlace privado revocable, token aleatorio almacenado como hash. Una nueva publicación sustituye y revoca los enlaces anteriores. |
| Decisión sin cuenta del cliente | Implementado | Aceptar, rechazar o pedir modificación con nombre y confirmación; una decisión por versión vigente. No es firma digital certificada. |
| Vista imprimible / PDF | Implementado | Impresión y guardado como PDF del navegador; no un servicio de generación/envío automático. |
| Trabajos | Implementado | Conversión de presupuesto aceptado sin duplicación, alta manual, lista/tablero, fechas, próxima acción, estados e historial. El tablero no tiene arrastre entre columnas. |
| Cambios de importe | Implementado | Exigen motivo y confirmación; no pueden reducir el acuerdo por debajo de los cobros vigentes. |
| Registro de cobros | Implementado | Anticipos/pagos parciales manuales, saldo, reintentos idempotentes y anulación con motivo. Rechaza sobrepagos. **No procesa dinero ni emite comprobantes fiscales.** |
| ARS y USD | Implementado | Una moneda por documento/trabajo; saldos y resúmenes separados. No hay conversión. |
| Panel de atención | Implementado | Pendientes de respuesta, acciones vencidas, entregas próximas/atrasadas, terminados con saldo y cobros del mes por moneda. No calcula ganancia. |
| WhatsApp | Implementado | Mensaje editable y enlace oficial para que el usuario decida enviarlo. Requiere teléfono internacional; no registra envío, recepción ni lectura. |
| Exportación CSV | Implementado | Clientes, presupuestos/versiones/decisiones, trabajos y movimientos del propio negocio; protección frente a fórmulas. No reemplaza un backup. |
| Demo sin registro | Implementado | Negocio y datos ficticios propios por visitante, acciones reales y vencimiento de 24 h. Sin cuenta administrativa compartida. |
| Feedback | Implementado | Pantalla, descripción, versión y contacto opcional; validación, CSRF, honeypot y límite por IP. No hay bandeja pública ni envío automático. |
| Separación de negocios | Implementado | Contexto de negocio en servidor, consultas por pertenencia y claves foráneas compuestas en las entidades operativas; pruebas de acceso por ID y exportación. No equivale a una auditoría independiente. |
| Auditoría y eventos de producto | Implementado | Registros separados; demos excluidas de eventos de uso real. Sin analítica externa ni evidencia de mercado validado. |
| Miembros e idiomas | Parcial | Modelo de membresía y archivos `lang/es`; no invitaciones, gestión de equipo ni selector de idioma. La interfaz inicial está en español y conserva textos en vistas. |
| Despliegue y mantenimiento | Parcial | Imagen de producción, MySQL privado, volúmenes, scheduler, configuración HTTPS y backup ensayados localmente. Falta proveedor/dominio y validación externa. |
| Recuperación de contraseña | Pendiente | No hay flujo ni correo transaccional implementados. |

Reglas detalladas, fórmulas y límites de importes/ítems: [alcance](docs/alcance.md). Diseño y accesibilidad revisada: [sistema visual](docs/diseno.md).

### Límites de esta beta

- No consigue clientes, procesa pagos, factura ante ARCA ni reemplaza contabilidad. Tampoco ofrece suscripciones, inventario completo, nómina, marketplace, chat, IA, apps nativas, modo sin conexión ni recordatorios automáticos.
- Un enlace publicado no demuestra entrega. Abrir WhatsApp no demuestra envío; abrir un presupuesto no demuestra lectura humana. La confirmación no verifica identidad ni garantiza validez jurídica.
- El importe solicitado como anticipo no constituye un cobro. Terminar/cancelar un trabajo no modifica por sí solo el saldo.
- No hay recuperación de cuenta por correo, roles avanzados, pantalla administrativa para feedback/pilotos ni baja de cuenta de autoservicio.
- Las políticas legales/privacidad y la operación del piloto necesitan revisión antes de un lanzamiento público. No se afirma cumplimiento normativo automático.
- No hay pruebas de carga, cobertura exhaustiva de accesibilidad o validación en Safari/Firefox/dispositivos físicos.

## Demo y testers

**URL pública verificada: no disponible.** No hay un hosting ni dominio autorizado conectados. Una tarea de nube o un servidor local no acreditan disponibilidad pública permanente. La vigencia de una futura URL, cuotas y suspensión por inactividad dependen del proveedor y siguen sin confirmar.

Con la instalación encendida, abrí la landing en el navegador local, puerto **8000**. Esa dirección es exclusivamente local, no un enlace para compartir con testers externos.

1. **Probar demo:** crea un espacio aislado con ejemplos. No necesita registro y sus cambios no afectan otras demos.
2. **Crear cuenta de prueba:** registrá tu propio negocio y elegí una contraseña personal. Los datos se conservan entre sesiones. Durante esta etapa usá nombres y contactos ficticios.
3. **Enviar feedback:** botón del aviso de beta o enlace del pie/errores. Elegí la pantalla, explicá qué pasó y dejá contacto solo si querés. No pegues contraseñas, enlaces privados ni datos de clientes.

### Recorrido breve reproducible

1. Entrá a la demo o creá una cuenta; agregá un cliente ficticio si tu negocio está vacío.
2. Creá un presupuesto de **ARS 1.000,00**, con un ítem, alcance y vencimiento futuro; dejá descuento e impuesto en cero.
3. Guardá, revisá el total y publicá una versión. Conservá el enlace que se muestra en ese momento; por seguridad no se recupera después.
4. Abrí el enlace en una ventana privada u otro perfil, sin la cuenta del propietario. Revisá la propuesta y aceptala con nombre ficticio y confirmación.
5. Volvé al negocio, abrí el presupuesto aceptado y creá el trabajo. Definí estado, fecha y próxima acción; consultá lista y tablero.
6. Registrá un cobro ficticio de **ARS 250,00**: el saldo esperado es **ARS 750,00**. No se mueve dinero. Podés anular el registro con motivo para comprobar el historial.
7. Exportá CSV o enviá feedback. Si grabás el recorrido, ocultá URL, enlace compartible, cookies y cualquier dato personal.

### Persistencia, vencimiento y restricciones

- Demo: vence a las 24 h; el scheduler elimina los datos vencidos en la siguiente ejecución horaria. Una sesión puede terminar antes: `SESSION_LIFETIME` es 120 minutos de inactividad por defecto; perder la cookie implica iniciar otra demo.
- Cuentas registradas: no hay borrado automático por inactividad durante el piloto. La política anunciada exige al menos 7 días de aviso antes de una limpieza/cierre para permitir exportar; es un compromiso operativo, no un envío automatizado implementado.
- Feedback: 90 días y siguiente limpieza diaria. La limpieza necesita el scheduler encendido.
- Hay límites separados por IP: registro/demo/feedback 5 por hora, ingreso 5 por minuto, solicitud de piloto 3 por hora y límites adicionales de enlaces/exportaciones. Pueden afectar repeticiones desde una red compartida.
- Una versión publicada vence al final del día configurado, en la zona horaria guardada. Revocar o sustituir un enlace impide usarlo; no lo publiques en redes.

## Base para crear contenido

Los hechos, fuentes, hipótesis, decisiones técnicas, desafíos documentados y atribución de trabajo están en [docs/CONTENIDO.md](docs/CONTENIDO.md). No contiene publicaciones listas para difundir ni resultados comerciales supuestos.

La [guía de capturas y demostraciones](docs/GUIA_CAPTURAS.md) propone pantallas existentes, formatos y datos ficticios. Antes de crear contenido, revisar esas fuentes y confirmar cualquier afirmación sobre intervención humana, usuarios o resultados.

## Instalación y ejecución

### Stack y requisitos

| Componente | Requisito / estado verificado |
| --- | --- |
| PHP | `^8.4.1` en Composer; verificado 8.4.27. BCMath es obligatorio para cálculos. |
| Framework | Laravel 12; lockfile actual 12.70.2. Monolito con Blade y JavaScript progresivo, sin SPA ni Alpine.js. |
| Base de datos | MySQL 8.4 para reproducir el entorno probado (8.4.13). SQLite se usa para pruebas rápidas, no como sustituto del despliegue MySQL. |
| Extensiones | PDO MySQL, BCMath, mbstring y las habituales de Laravel: ctype, curl, DOM/XML, fileinfo, filter, hash, OpenSSL, PCRE, PDO, session y tokenizer. PDO SQLite adicional para la suite en memoria. Los Dockerfiles preparan las extensiones de su flujo. |
| Composer | Versión 2 y `composer.lock`; no actualizar dependencias para una instalación reproducible. |
| Frontend | Node.js 22.12+; recomendado 24, verificado 24.19.0 con npm 11.9.0. Tailwind 4.3.3, Vite 7.3.7 y Playwright 1.64.1 según lockfile. |
| Docker | Engine con Compose v2 y soporte de `up --wait`/BuildKit. Node/npm se usan en el host para compilar assets. |

### Variables de entorno

Partí de [.env.example](.env.example) en local y [.env.production.example](.env.production.example) para el perfil de producción. **No subir `.env`, claves, contraseñas, volcados ni cookies al repositorio.** Los ejemplos no contienen valores secretos.

| Variables | Uso y configuración |
| --- | --- |
| `APP_NAME`, `APP_RELEASE` | Nombre e identificador visible de prueba. La versión tiene un valor por defecto en `config/beta.php`. |
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | Entorno, detalle de errores y URL base. En público: producción, debug desactivado y URL HTTPS real. |
| `APP_KEY` | Secreto de Laravel; generarlo una vez y respaldarlo. No regenerarlo al reiniciar ni mostrarlo en pantalla. |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexión MySQL. En Compose, el host es el servicio `db`; en una instalación nativa, usar el servidor real. No publicar la base en Internet. |
| `DB_ROOT_PASSWORD` | Inicialización/administración del contenedor MySQL; no es la contraseña utilizada por la aplicación. |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE` | Idioma inicial y generación de ejemplos; no habilitan por sí solas una interfaz multilingüe. |
| `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_ENCRYPT`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE` | Sesiones en base, duración y seguridad. Cookie segura en HTTPS público; en local HTTP el ejemplo la deja desactivada. |
| `CACHE_STORE`, `QUEUE_CONNECTION` | Caché compartida en base y ejecución síncrona. Este flujo no necesita un worker de colas. |
| `LOG_CHANNEL`, `LOG_LEVEL` | Destino/nivel de logs. Producción usa stderr y filtrado; evitar cuerpos de solicitudes y tokens. |
| `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Configuración de Laravel; no hay correo transaccional implementado. Un mailer de log no significa entrega. |
| `BETA_DOMAIN`, `TRUSTED_PROXIES` | Dominio del perfil HTTPS y proxies que Laravel puede confiar. No usar un dominio ficticio como URL publicada ni confiar en cualquier proxy. |
| `LOCAL_UID`, `LOCAL_GID` | Opcionales en Compose local para permisos del checkout; valores por defecto 1000. |
| `PLAYWRIGHT_BASE_URL`, `PLAYWRIGHT_CHROMIUM_EXECUTABLE` | Opcionales para pruebas de navegador: destino autorizado y binario Chromium. |

`compose.production.yaml` fija varios valores de seguridad, base y proxy; no todos los campos se toman del archivo de secretos. Revisar ese perfil si se cambia de red/proveedor. Detalle: [despliegue](docs/despliegue.md).

### Instalación local con Docker (recomendada)

Desde la raíz del checkout actual:

```sh
# Conservar un .env existente; copiar solo cuando no exista.
test -f .env || cp .env.example .env
# Completar privadamente DB_PASSWORD y DB_ROOT_PASSWORD con valores distintos.
docker compose build app
docker compose up -d --wait db
docker compose run --rm --no-deps app composer install --no-interaction
npm ci
npm run build
```

**Solo para una instalación nueva con `APP_KEY` todavía vacío**, generar la clave. Omitir este comando si ya existe una válida:

```sh
docker compose run --rm --no-deps app php artisan key:generate
```

Aplicar esquema y arrancar:

```sh
docker compose run --rm --no-deps app php artisan migrate --force
docker compose up -d app scheduler
```

El servicio local expone únicamente el puerto 8000 de loopback; MySQL no publica puertos. `scheduler` ejecuta la limpieza horaria de demos y diaria de feedback. No ejecutar `down -v` si se quieren conservar los datos de los volúmenes.

**Datos de ejemplo opcionales:**

```sh
docker compose run --rm --no-deps app php artisan db:seed
```

El seeder solo admite `local/testing`, es repetible y crea un negocio de ejemplo separado, excluido de métricas y sin vencimiento automático. No carga datos en la cuenta personal de cada tester. No se publican sus credenciales aquí: para probar la interfaz usá **Probar demo** o registrá tu propia cuenta. No ejecutar el seeder en producción.

En Linux ajustar `LOCAL_UID`/`LOCAL_GID` al usuario propietario si corresponde. `storage` y `bootstrap/cache` necesitan permisos de escritura; no usar 777. Para cambios de assets con la CSP actual:

```sh
npm run build -- --watch
```

Recargar el navegador. `npm run dev`/`composer dev` existen, pero el servidor Vite de desarrollo no está habilitado por la CSP actual; no se recomiendan como arranque verificado de esta beta.

### Entorno administrado de nube

En este entorno no hay PHP nativo; se utiliza la imagen Docker. Los helpers tienen la ruta `/workspace/TrabajoClaro` fija y requieren Docker, Python 3, curl y ripgrep:

```sh
bash scripts/cloud-install.sh
bash scripts/cloud-start.sh
```

El primero conserva `.env` y `APP_KEY` existentes, instala desde lockfiles y migra. El segundo arranca base, aplicación y scheduler y comprueba la landing. Cada tarea ya está aislada: usar el checkout existente, sin crear worktrees salvo pedido explícito.

Para una construcción manual detrás del proxy administrado:

```sh
docker build --secret id=proxy_ca,src="$CODEX_PROXY_CERT" \
  -f docker/Dockerfile -t trabajoclaro-php .
docker compose run --rm --no-deps \
  -v /etc/ssl/certs/ca-certificates.crt:/run/ca.pem:ro \
  -e COMPOSER_CAFILE=/run/ca.pem -e GIT_SSL_CAINFO=/run/ca.pem \
  -e COMPOSER_HOME=/app/.composer-local \
  app composer install --prefer-source --no-interaction
```

El acceso por Git HTTPS fue la alternativa al bloqueo de la API de GitHub. Mantener proxy y TLS verificado; no distribuir `.composer-local`. Guardar instrucciones o publicar un snapshot del entorno no equivale a desplegar la aplicación para testers.

### Instalación nativa

Con PHP/extensiones, Composer, Node y MySQL propios: preparar un usuario/base privados, conservar o copiar `.env.example`, configurar el host de base (no `db` salvo que ese nombre resuelva), ejecutar `composer install`, `npm ci`, `npm run build`, generar clave **solo si falta**, y ejecutar `php artisan migrate`. Seeder opcional solo local.

Para desarrollo usar `bash scripts/serve.sh` y, en otra terminal desde la raíz, `php artisan schedule:work`. El helper del servidor usa el puerto 8000 y oculta tokens de rutas privadas en su salida; necesita Bash y sed. Ninguno de estos servidores es para producción. La instalación nativa no se ejecutó en esta revisión.

## Pruebas y comprobaciones

```sh
# SQLite efímero en memoria: no usar la base de trabajo.
docker compose run --rm --no-deps app php artisan test
# Consistencia y formato.
docker compose run --rm --no-deps app composer validate --strict
docker compose run --rm --no-deps app vendor/bin/pint --test
npm run build
```

Para MySQL, un administrador debe crear **la base separada `trabajoclaro_test`** y otorgar acceso al usuario de pruebas. Nunca apuntar `RefreshDatabase` a datos reales:

```sh
docker compose run --rm --no-deps app vendor/bin/phpunit -c phpunit.mysql.xml
```

Usar ese archivo XML dedicado, no confiar en cambiar solo variables al ejecutar la configuración SQLite por defecto. No almacenar configuración de producción en el runner; limpiar una configuración cacheada antes de apuntar a la base de pruebas.

Con el servidor local encendido y Chromium instalado (por defecto `/usr/bin/chromium`):

```sh
npx playwright test --config=playwright.config.js --list
npx playwright test --config=playwright.config.js
```

Las pruebas crean **demos, cuentas propias y feedback ficticios**, que pueden persistir en la base del destino; no son solo pruebas de lectura. Capturas y PDF quedan en `test-results/`, ignorado. Solo cambiar `PLAYWRIGHT_BASE_URL` a un destino beta autorizado. Los límites por IP pueden afectar ejecuciones repetidas; no desactivar controles en un servicio público para hacer pasar pruebas.

**Comprobación repetida al cerrar el alcance:** 34 pruebas / 333 comprobaciones aprobadas en SQLite y en MySQL; compilación Vite y 8 recorridos Chromium aprobados, incluidos marca, variantes, eslogan y carga de iconos/fuente. La firma se verificó en móvil/escritorio, con foco visible, y en los PDF público e interno: texto exacto, una sola aparición y anotación del enlace al portfolio. También se comprobó su ausencia en los cuatro CSV. La construcción de producción y backup/restauración no se repitieron para este cambio; sus resultados anteriores están en el [informe beta 2](docs/beta-2-verificacion.md).

La suite verifica pertenencia por negocio, acceso por IDs, CSV seguro, cálculos/redondeos, versiones, decisiones inválidas/duplicadas/vencidas, conversión idempotente, pagos/anulaciones/saldo, monedas, CSRF, escape de contenido, demo, feedback y retención. La suite de navegador cubre registro/ingreso, carga/confirmación de feedback, recorrido de demo y PDF a 360×800 y 1440×1000. No hay porcentaje de cobertura medido ni certificación de seguridad/accesibilidad; no se ensayó alta concurrencia ni un hosting público real.

## Despliegue, respaldo y mantenimiento

La [guía de despliegue](docs/despliegue.md) incluye opciones compatibles, construcción de imagen, configuración de secretos, migraciones controladas, HTTPS, comprobación pública y restauración. El perfil [compose.production.yaml](compose.production.yaml) usa Apache/PHP, MySQL privado, storage y certificados persistentes, scheduler y Caddy. La imagen necesita previamente la imagen base `trabajoclaro-php`, `vendor` y `public/build`; no es un build autónomo de un checkout sin preparar.

No se ejecutan migraciones automáticamente al iniciar producción. Antes de habilitar una versión: respaldo, revisión/aplicación de migraciones, assets y comprobaciones desde el dominio real. Mantener debug desactivado, cookies seguras, `public/` como raíz, logs sin tokens y `.env`/claves fuera del repositorio. El perfil de prueba HTTP de `docker/compose.smoke.yaml` no es un preview público.

Para un despliegue Docker ya configurado, con secretos y directorio de respaldo **fuera del checkout**:

```sh
bash scripts/backup-production.sh /ruta/protegida/beta.env /directorio/privado/backups
```

Las rutas son marcadores que deben reemplazarse. El script genera un dump comprimido con permisos privados; **no lo cifra, no lo sube y no programa copias por sí solo**. Configurar almacenamiento externo autorizado, cifrado, frecuencia y retención. Respaldar también `APP_KEY`/configuración de forma segura y storage si se incorporan archivos de usuario.

Restaurar primero en una **base nueva** con MySQL, mediante un archivo de opciones protegido o gestor de secretos; verificar documentos, decisiones, cobros, saldos y feedback antes de considerar un cambio de base activa. El ensayo anterior comprobó recuentos restaurados con datos ficticios; no acredita recuperación ante desastre de un proveedor real. Procedimiento y límites en [despliegue](docs/despliegue.md).

Comandos operativos existentes (dentro del contenedor `app`, o con PHP nativo): `php artisan migrate:status`, `php artisan schedule:list`, `php artisan demo:prune` y `php artisan feedback:prune`. Los dos últimos eliminan los datos vencidos indicados; no son comandos para limpiar cuentas de testers registrados.

## Estructura del repositorio

```text
app/Http/Controllers/     Registro, operaciones, documento público, CSV y feedback
app/Http/Middleware/      Contexto de negocio y encabezados de seguridad
app/Models/               Negocios, membresías y entidades del recorrido
app/Services/             Dinero, Workflow, demo, CSV e identificación de pantalla
app/Logging/              Filtrado de logs
config/                   Laravel y configuración de beta
routes/                   Web, comandos y tareas programadas
database/                 Migraciones, factories y seeder local
resources/views/          Blade, componentes, pantallas y documento imprimible
resources/css/            Sistema visual y adaptación móvil
resources/js/             Interacciones progresivas y estados de envío
lang/es/                  Textos de interfaz/autenticación/validación
public/                   Raíz web; build generado por Vite
tests/                    Pruebas Feature y Browser
docker/                   Runtime local, producción, PHP/Apache y Caddy
scripts/                  Arranque de nube, servidor local y respaldo
.github/workflows/        CI configurada; ejecución remota no comprobada
docs/                     Alcance, contenido, capturas, diseño e informes
```

## Identidad de marca

Nombre provisional **TrabajoClaro**; eslogan **Cada trabajo en orden. Cada cobro, claro.** La identidad está integrada en landing, acceso, registro, panel, demo y documentos públicos. El componente [brand-logo](resources/views/components/brand-logo.blade.php) usa SVG escalables y texto HTML para el eslogan; los tokens de color/tipografía están en `resources/css/app.css`.

La muestra local está en la ruta **`/marca`** de la aplicación. SVG y grilla en [public/brand](public/brand); font Manrope local, licencia OFL y procedencia en [public/fonts/manrope](public/fonts/manrope). Hay favicon SVG/ICO, PNG 16/32, Apple 180 e iconos 192/512. El manifest usa `display: browser`: no hay modo offline ni promesa de instalación como PWA.

[Búsqueda del nombre, variantes, archivos y verificación](docs/MARCA.md). Las consultas a buscador y registros de dominios fueron bloqueadas por el proxy: **disponibilidad no determinada**, sin registro de marcas/dominios. Se mantiene el nombre provisional y la firma del desarrollador separada.

## Atribución visible

La firma **DESARROLLADO POR NICOLAS AGUIRRES** enlaza directamente al [portfolio del autor](https://nicolasaguirrescode.com/). El componente Blade [author-signature](resources/views/components/author-signature.blade.php) centraliza texto, destino y atributos del enlace. El [layout](resources/views/components/layout.blade.php) lo incluye en el pie de landing, acceso/registro, panel, pantallas internas, demo y presupuesto público; las páginas de error también lo reutilizan.

En web abre otra pestaña con `target="_blank"` y `rel="noopener noreferrer"`, texto real, contraste y foco de teclado. Permanece en el flujo del documento, sin posiciones fijas ni superposición. El layout recibe `printable` en las vistas del presupuesto: al imprimir oculta el resto del pie y conserva la firma al final del documento. El PDF del navegador conserva el enlace cuando su generador admite anotaciones; otros visores/impresoras pueden variar. No se agrega a CSV, dumps, logs ni archivos técnicos generados. Para no exponer el enlace privado al imprimir, revisar además los encabezados/pies propios del navegador.

## Evolución sin fechas comprometidas

1. Conectar un destino autorizado y verificar HTTPS, aislamiento, persistencia, cron y restauración en el proveedor real.
2. Realizar pilotos acompañados; registrar evidencia de problema, activación, uso repetido y disposición a pagar, sin confundir pruebas ficticias con adopción.
3. Revisar políticas legales/privacidad y atención de cuentas; evaluar recuperación de contraseña con correo real antes de implementarla.
4. Completar las revisiones en dispositivos/navegadores y las pruebas de accesibilidad/concurrencia según los resultados del piloto.
5. Evaluar, sin compromiso de desarrollo, invitaciones y traducción completa si aparece una necesidad validada. La estructura parcial no significa que ya existan.

Facturación fiscal, procesamiento de pagos y las demás funciones excluidas no se prometen como parte de este roadmap.

## Documentación relacionada

- [Contenido: hechos y evidencia](docs/CONTENIDO.md) · [Guía de capturas](docs/GUIA_CAPTURAS.md).
- [Alcance y reglas](docs/alcance.md) · [Diseño](docs/diseno.md) · [Piloto de cinco minutos y preguntas](docs/piloto.md).
- [Despliegue y recuperación](docs/despliegue.md) · [Informe beta 2](docs/beta-2-verificacion.md).
- [Informe inicial, histórico](docs/verificacion.md) · [Changelog](CHANGELOG.md).

Los informes anteriores describen verificaciones locales realizadas en esa etapa; no sustituyen el estado revisado aquí ni acreditan una publicación externa.
