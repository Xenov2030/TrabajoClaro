# Instalación web de TrabajoClaro

Revisión: 2026-11-01 (Argentina). La instalación web está implementada; la comprobación completa en teléfonos físicos y sesión instalada sigue pendiente. **No es un APK ni un ejecutable, y requiere conexión.**

## Cómo instalar

Página del entorno publicado: https://trabajo-claro-orpin.vercel.app/instalar. Esta ruta y sus recursos PWA se comprobaron públicamente por HTTPS después de publicar la implementación. El acceso normal por navegador se conserva.

- En la landing hay «Instalar TrabajoClaro» y en el pie «Instalar app». Si el navegador emite `beforeinstallprompt`, el botón solicita su diálogo nativo **solo tras pulsarlo**. Si no, abre instrucciones; no simula una instalación.
- Chrome/Edge Android: menú → Instalar aplicación o Añadir a pantalla de inicio. Si solo ofrece acceso directo, puede abrir una pestaña; no confundirlo con standalone.
- iPhone/iPad: Safari → Compartir → Añadir a pantalla de inicio. Mantener «Abrir como app web» si aparece.
- Chrome/Edge escritorio: icono de instalación de la barra de direcciones o menú. Safari para Mac compatible: Archivo → Añadir al Dock. Otros navegadores pueden no ofrecer instalación; seguir usando la web.
- No se muestran ventanas de promoción repetidas, no se inicia el diálogo al cargar y cancelar no bloquea la aplicación. Si se detecta modo standalone o `appinstalled`, no se vuelve a ofrecer el botón.

## Implementación

- [Manifest](../public/site.webmanifest): `id: /`, `name/short_name: TrabajoClaro`, `start_url: /`, `scope: /`, `display: standalone`, colores de la marca. Sin tokens, cuenta o datos personales en el punto de inicio.
- Iconos PNG reales 192/512 para `any`, variantes 192/512 `maskable` opacas y Apple touch icon 180. El símbolo esencial se mantiene dentro del círculo seguro central de radio 40% del tamaño. Generador: [render-brand-icons.mjs](../scripts/render-brand-icons.mjs).
- [InstallProvider/InstallAction/InstallPage](../src/components/install.js) centralizan evento nativo, estado, instrucciones y actualizaciones; [layout](../src/app/layout.js) aporta el proveedor y metadatos Apple.
- [Service worker](../public/sw.js): **sin CacheStorage, precache, fallback offline, notificaciones ni sincronización**. Solo reenvía GET de recursos estáticos públicos del mismo origen a `fetch`; respeta la caché HTTP ordinaria de archivos públicos. No intercepta navegación, documentos, Auth/Supabase, respuestas autenticadas ni escrituras.
- `/sw.js` se sirve sin caché y con ámbito `/`. No se llama a `skipWaiting`, `clients.claim` ni recarga automática. Una actualización espera su activación normal; `/instalar` informa si hay una esperando. Guardar formularios antes de cerrar todas las ventanas y volver a abrir. Sin autosave o recuperación tras cierre prometidos.
- Instalar no evita vencimiento de demo, cierre de sesión, cuotas o suspensión del proveedor. La sesión puede compartirse con el navegador o pedir nuevo ingreso según plataforma. Los datos permanecen en Supabase, no en el icono instalado.
- Se corrigió el retorno `/auth/callback`: después de obtener sesión válida pasa a `/app`; si no hay sesión se informa el error y se ofrece reintentar el acceso. La prueba de integración usa sesión preemitida local; no acredita por sí sola Google alojado.

## Verificación y límites

| Verificación | Evidencia |
| --- | --- |
| Manifest/ámbito/iconos | Pruebas de navegador validan identidad estable, standalone, raíz sin tokens, tamaños PNG reales y carga de variantes; Pillow comprueba dimensiones/margen seguro. |
| Worker activo y privacidad | Registro/control de página comprobados en Chromium; CacheStorage vacío. Pruebas ejecutan el worker con solicitudes privadas/externas/POST y fallan si las intercepta o accede a caché/recarga. |
| Instalación por acción | Evento de prueba comprueba que no se llama a `prompt` al cargar, se llama una vez al pulsar y cancelar devuelve las instrucciones. **No es prueba del diálogo nativo real.** |
| iOS/estado instalado | Detección/instrucciones y estado de evento emulados. No equivalen a instalar en un iPhone real. |
| Actualizaciones | Pedir actualización del worker conserva el texto del formulario; no hay cambio forzado de worker ni recarga. |
| Chromium nativo local | Perfil persistente no incógnito: `Page.getInstallabilityErrors` devolvió **cero errores**. Se intentó `PWA.install` y Chromium lo rechazó con **`kWebAppDisabled`** en este entorno, también usando plataforma virtual de ventanas. No se pudo completar instalación real ni apertura standalone. |
| Flujo con DB | Prueba de PostgreSQL/PostgREST real incluye retorno automático desde callback y presupuesto → aceptación independiente → trabajo → cobro. No se ejecuta desde una app instalada porque la instalación nativa fue rechazada. |

Las pruebas de UI/criterios no acreditan una instalación completa. Hace falta verificar manualmente el diálogo, icono/pantalla de inicio, apertura standalone, login/logout/Google y navegación desde la app instalada en Android, iOS y escritorio disponibles. No se declara acceso offline ni sesión instalada comprobada.

Resultados locales de esta implementación: **7 pruebas unitarias**, **10 pruebas de navegador móvil/escritorio** y **2 recorridos con PostgreSQL real aprobados**, además de build Next.js aprobado. Los casos de instalación emulados y el intento nativo rechazado se distinguen en la tabla anterior.

## Comprobación del deployment público

El 2026-11-01 se abrió `/instalar` en Chromium con perfil persistente a 360×800 y 1440×1000: cero errores de instalabilidad, sin desborde horizontal ni errores JS detectados; logo/firma cargados. Worker activo en `/sw.js`, alcance `/`, CacheStorage vacío y respuesta sin caché. El maskable público respondió HTTP 200 y es PNG real 512×512. No se disparó un evento de instalación falso para esta comprobación, ni se completó instalación nativa. [Reporte sin credenciales ni datos personales](evidencia/pwa-2026-11-01.json).

## Guion de prueba manual

1. Desde el dominio HTTPS público, abrir `/instalar` en un navegador normal (no incógnito). No iniciar instalación mientras hay formularios sin guardar.
2. Pulsar Instalar si el navegador lo ofrece, o seguir su menú. Confirmar el diálogo real y comprobar el icono de TrabajoClaro.
3. Cerrar y abrir desde ese icono: debe abrir dentro del alcance del sitio y, si la plataforma lo admite, sin la barra de pestañas. La página informa que ya se usa en modo aplicación.
4. Ingresar con cuenta de prueba/Google autorizado o crear demo. Crear un documento ficticio y navegar; salir y comprobar que no muestra datos privados. Volver a ingresar y comprobar aislamiento y persistencia según tipo de cuenta.
5. Guardar un formulario antes de cerrar para recibir una actualización. Comprobar que no se fuerza la recarga durante una edición.
6. Desconectar la red solo después de guardar: **no debe prometer que operaciones se guardan offline**. Reconectar y revisar la lista antes de reenviar una solicitud de resultado incierto.

Reportar navegador/dispositivo/versión y pasos, sin contraseñas, códigos, tokens o enlaces privados. El enlace depende de la disponibilidad de Vercel/Supabase; caducidad, cuotas y suspensión continúan sujetos a sus planes reales.
