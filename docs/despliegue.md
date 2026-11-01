# Beta para testers: despliegue y acceso público

## Estado real

No hay una URL pública publicada. En este entorno no hay destino de preview, cuenta de hosting, credenciales de despliegue ni identidad de nube conectados. Hay Docker, pero ejecutar contenedores dentro de una tarea no crea un hosting público permanente. No se contrató ningún servicio ni se modificó un sitio existente.

Se construyó y probó una imagen **de producción real** con Apache/PHP 8.4, Laravel completo y MySQL persistente. Se ensayó por loopback, sin presentar esa dirección como preview. Caddy pasó su validación de configuración para HTTPS; falta un dominio real y un servidor autorizado para obtener el certificado y comprobar acceso externo.

## Qué destino sirve

| Opción | Compatibilidad y condición |
| --- | --- |
| Hosting PHP/MySQL administrado, por ejemplo Hostinger | Verificar PHP **8.4.1+**, extensiones BCMath/PDO MySQL/mbstring/DOM, MySQL, acceso para migraciones, cron, HTTPS, `public/` como raíz y almacenamiento persistente. Un plan existente puede servir; no asumir que todos lo permiten. |
| VPS dedicado a esta beta con Docker | El perfil incluido ejecuta la aplicación completa, MySQL privado, scheduler y Caddy con HTTPS. Requiere mantener el servidor y sus respaldos. No usar puertos o dominios de otros sitios sin autorización. |
| Plataforma de contenedores, por ejemplo Railway | Debe tener servicio PHP/contenedor, MySQL y volumen persistente, procesos programados y un hostname HTTPS. Revisar precio, cuotas, suspensión e inactividad antes de autorizar. No se creó ningún recurso. |
| Vercel | No es el destino directo preparado: Laravel/PHP y MySQL necesitan un adaptador y servicios externos, con restricciones de ejecución y persistencia. Este proyecto está preparado como monolito PHP o contenedor. No se sustituyó el backend por una página estática. |

Para continuar hace falta **un servicio existente autorizado**, el acceso conectado de forma segura, y un dominio/subdominio o hostname HTTPS proporcionado por ese servicio. No ingresar claves en el chat. Si el proveedor implica contratar o ampliar un plan, hace falta autorización del costo antes de hacerlo.

La duración de la URL, cuotas y posible suspensión por inactividad dependen del destino elegido y todavía **no están confirmadas**. El perfil de VPS no programa suspensión por inactividad; eso no garantiza disponibilidad del proveedor ni evita mantenimiento.

## Preparación: imagen inmutable

Los recursos de frontend y dependencias se preparan desde los lockfiles. La imagen de producción excluye `.env`, `.env.*`, credenciales, directorios Git, cachés de Composer, node_modules, pruebas y capturas. La etapa de dependencias elimina los paquetes de desarrollo en una copia; no modifica el vendor del checkout.

```sh
# En la máquina de construcción, desde el checkout actual.
docker build -f docker/Dockerfile -t trabajoclaro-php .
docker compose run --rm --no-deps app composer install --no-interaction
npm ci
npm run build
docker build -f docker/production.Dockerfile -t trabajoclaro-beta:2026.10-beta.2 .
```

En este entorno administrado agregá `--secret id=proxy_ca,src="$CODEX_PROXY_CERT"` a ambos builds y usá el comando Composer con CA/proxy descrito en README. No desactivar TLS. Los artefactos de esta versión ya se construyeron. Actualizar `APP_RELEASE` en cada nueva versión y usar la misma etiqueta de imagen (hasta 80 caracteres, letras/números/puntos/guiones/guion bajo).

## VPS nuevo o destino aislado autorizado

1. Crear un archivo de secretos **fuera del repositorio y del directorio web**, con permisos 600, tomando las claves de `.env.production.example`. Configurar dominio real, `APP_KEY` aleatoria de 32 bytes codificada como `base64:...`, contraseña de usuario MySQL y contraseña raíz distintas. Mantener la clave estable y respaldarla con seguridad. No guardar valores en Git, logs o comandos visibles.
2. Verificar DNS apuntando al servidor y disponibilidad de 80/443 (TCP) y opcionalmente 443/UDP. Verificar que la red Docker `172.30.84.0/24` no esté ocupada. Si se cambia, actualizar también `TRUSTED_PROXIES` del perfil; no confiar en cualquier proxy.
3. Transferir imagen/código y `compose.production.yaml`, `docker/Caddyfile`. Si hay un registro privado, conectar la identidad autorizada por su mecanismo seguro; todavía no se eligió ninguno.
4. Iniciar solo la base y esperar disponibilidad:

```sh
docker compose --env-file /ruta/protegida/beta.env -f compose.production.yaml up -d --wait db
```

5. En actualizaciones, crear backup antes de migrar. Ejecutar la migración explícitamente, revisando el resultado. **No hay migración automática en el entrypoint**:

```sh
docker compose --env-file /ruta/protegida/beta.env -f compose.production.yaml run --rm --no-deps app php artisan migrate --force
```

6. Habilitar aplicación, cron y HTTPS:

```sh
docker compose --env-file /ruta/protegida/beta.env -f compose.production.yaml up -d app scheduler edge
```

Caddy obtiene el certificado del dominio configurado y redirige HTTP a HTTPS. No se ejecutó `edge` en el ensayo local para no simular un dominio público. La única entrada pública es Caddy: MySQL y PHP no publican puertos en el perfil normal. La base, storage de Laravel y certificados/configuración de Caddy usan volúmenes persistentes. No ejecutar `down -v` sobre datos del piloto.

La aplicación fuerza producción/debug off/cookies seguras/sesiones cifradas. Los secretos llegan por entorno a los contenedores, no por un archivo dentro de la imagen. Apache no registra URI, referer, cookies ni IP. Caddy filtra la información de request en errores. El log de Laravel omite mensajes/datos de excepciones y registra solo tipo, código y ubicación; otros campos sensibles se ocultan. No habilitar APM, captura de cuerpos o analítica de enlaces privados sin configuración expresa.

En hosting PHP administrado sin Docker, usar el mismo código y assets, crear una base privada, configurar `.env` fuera de la raíz pública, servir `public/` por HTTPS y ejecutar Composer/migraciones/cron según README. Aplicar la misma supresión de logs de tokens en su servidor/proxy. Verificar primero los requisitos del plan.

## Verificación obligatoria desde la URL pública

Una vez disponible el destino, usar **la URL HTTPS exacta entregada por el proveedor o dominio autorizado**, nunca un placeholder. Ejecutar:

```sh
PLAYWRIGHT_BASE_URL=https://DOMINIO_AUTORIZADO npx playwright test --config=playwright.config.js
```

`DOMINIO_AUTORIZADO` es un marcador, no una URL publicada. La suite crea cuentas y datos ficticios de testers, guarda capturas locales y no envía WhatsApp ni correos. Requiere Chromium y crea registros persistentes: ejecutar solo en el entorno beta autorizado. Antes de repetirla muchas veces, considerar los límites por IP; no desactivar la protección en un servicio público.

Checklist adicional:

- Comprobar certificado válido y redirección HTTPS desde otra red/dispositivo.
- Landing, demo aislada sin registro, registro/login con negocio propio, feedback con confirmación.
- Crear/publicar presupuesto; abrirlo en una sesión separada; aceptar; convertir; registrar cobro parcial y verificar saldo.
- Repetir con un segundo tester y probar IDs ajenos/exportación; las pruebas funcionales verifican esto en ambas bases.
- Reiniciar servicios y confirmar que cuentas/documentos permanecen; validar cron, backup y restauración.
- Inspeccionar solo presencia/estado de secretos, debug off, sesiones seguras, puertos privados y logs sin tokens.
- Confirmar y comunicar vencimiento del enlace, límites e inactividad **del plan real**.

Nada de esta lista se declara comprobado en un sitio público mientras no exista el destino. El ensayo local con la configuración de producción ya aprobó los seis recorridos de navegador y persistencia tras reiniciar contenedores.

## Respaldo y recuperación

En el perfil Docker hay un comando probado que guarda un dump comprimido con permisos privados y detecta errores del dump:

```sh
bash scripts/backup-production.sh /ruta/protegida/beta.env /directorio/privado/backups
```

No guardar backups en el checkout ni en `public/`. Cifrar y enviar a un almacenamiento de respaldos autorizado, fuera del servidor; se necesita elegir destinatario/clave/servicio seguro antes de automatizar esa transferencia. El script no sube nada ni contrata servicios. Programar backup diario y antes de migraciones; acordar retención de copias (propuesta inicial: 7 diarias y 4 semanales, pendiente de la política del piloto). Guardar aparte `APP_KEY` y configuración de forma segura.

Restaurar siempre **primero en una base nueva**. Crear una base con nombre explícito, descomprimir el dump y cargarlo con el cliente MySQL usando autenticación por archivo seguro o entorno; no poner contraseñas en argumentos. Comprobar usuarios, documentos, decisiones, cobros, saldo y feedback. Cambiar la base activa solo después de verificar y planificar el corte. Respaldar también el volumen storage si en el futuro hay archivos de usuario; hoy no hay adjuntos.

Se ensayaron dump y restauración con datos ficticios en `trabajoclaro_restore_check`, sin reemplazar la base del ensayo. Coincidieron los recuentos de usuarios, trabajos, movimientos y feedback. Esa prueba local no sustituye un ensayo con el almacenamiento/host reales del piloto.

## Revertir un despliegue

Procedimiento preparado, **no ensayado de extremo a extremo**. Antes de actualizar, conservar la etiqueta/digest anterior, configuración y `APP_KEY`, y tomar un backup verificado fuera de la raíz web. Revisar previamente si las migraciones son compatibles con ambas versiones.

Si falla una actualización, detener las escrituras mediante mantenimiento y evaluar primero la compatibilidad del esquema. Si es compatible, seleccionar la imagen anterior mediante `APP_RELEASE` en el archivo protegido, recrear `app` y `scheduler`, comprobar `/up` y el recorrido con datos ficticios y recién entonces reabrir acceso. Conservar logs seguros para investigar. No ejecutar `down -v`.

**Revertir código no revierte datos.** No ejecutar `migrate:rollback` a ciegas: un `down()` puede eliminar tablas/columnas y perder información. Si la migración no es compatible, preferir una corrección hacia adelante; restaurar backup en una base nueva solo con validación, corte acordado y evaluación de los cambios posteriores al backup que se perderían. No sustituir la base activa ni borrar la anterior sin autorización específica. La restauración local de prueba no acredita que este rollback operativo ya esté probado.
