# Estado consolidado · migración a Next.js/Supabase

Revisión 2026-11-01, Argentina. Versión de código `0.2.0-beta.1`; beta pública con recorrido de demo verificado en https://trabajo-claro-orpin.vercel.app/. No versión estable ni cumplimiento total del alcance. El pedido posterior autorizó cambiar de Laravel/MySQL a Next.js/PostgreSQL de Supabase para Vercel; no autoriza contratar servicios ni modificar Jornadapp.

La lista exhaustiva de pedidos anteriores está conservada en [el cierre Laravel](legacy-laravel/ESTADO_BETA.md). **Sus 34 pruebas PHP y ocho recorridos anteriores no acreditan la migración.** Esta matriz actualiza el estado vigente de esos grupos de requisitos. No se presenta una pantalla como función completa.

Estados: **Implementado y verificado** (entorno/prueba explícitos); **Implementado, pendiente de verificar** (código sin comprobación externa necesaria); **Parcial** (parte disponible con faltante); **Pendiente**; **Fuera de la primera beta**.

| Requisito | Estado vigente | Evidencia o límite |
| --- | --- | --- |
| Preparación de entorno/repositorio sin descartar trabajo | Implementado y verificado | Laravel conservado en `legacy/laravel` y Git. MySQL/volúmenes no borrados. Nuevo npm lock y configuración Next.js. No importación de datos MySQL automática. |
| Nombre/eslogan/público/hipótesis, sin estadísticas o testimonios inventados | Implementado y verificado | Landing/README: pequeños negocios, recorrido y límites; necesidad comercial aún no validada. |
| Marca propia, SVG horizontal/eslogan/símbolo/claro/oscuro/mono, Manrope OFL, tokens | Implementado y verificado | Activos conservados en `public`, componentes Next.js, `/marca`, pruebas visuales públicas. Eslogan HTML donde corresponde. |
| Favicon SVG/ICO/PNG, Apple 180, iconos 192/512 | Implementado y verificado | Archivos reales conservados y carga en navegador; no todas las barras de sistema/dispositivos físicos. |
| Disponibilidad de nombre/dominios | Parcial | Intentos externos bloqueados previamente; sin conflicto confirmado ni disponibilidad legal determinada. No registro de marca/dominio. |
| Firma exacta del autor, portfolio, foco, nueva pestaña y enlace PDF | Implementado y verificado | Componente único `Author`, todas las rutas, impresión PDF; sin firma en CSV/técnicos. Autor separado del logo de producto. |
| Landing y solicitud de piloto persistida/protegida | Implementado y verificado | Formulario RPC `tc_send_pilot`, validación y honeypot; escritura en prueba SQL. Solicitud almacenada también desde navegador público; no correo automático. |
| Registro, login/logout, Google, confirmación de correo | Implementado, pendiente de verificar | Alta anónima y logout verificados públicamente. Registro/login por correo y entrega/confirmación siguen pendientes. Google responde proveedor deshabilitado (prueba pública 2026-11-01); [configuración requerida](GOOGLE.md), sin login completo acreditado. |
| Negocio: nombre, rubro, país, zona y moneda | Implementado y verificado | Setup/settings autorizados por UID; configuración SQL probada y panel con negocio en navegador local. |
| Miembros/invitaciones/traducción integral | Parcial | Un propietario por negocio, sin equipo ni selector de idioma. Interfaz en español argentino. |
| Demo directa sin credenciales compartidas y aislada | Implementado y verificado | Alta anónima y recorrido públicos móvil/escritorio; dos demos no pueden leer ni mutar IDs ajenos. Expiración/poda verificadas en SQL local, cron alojado pendiente. |
| Clientes: crear, editar, buscar, archivar sin borrar historia | Implementado y verificado | RPC/RLS y flujo de navegador con base real; archivado no elimina registros. Historial visual de movimientos por cliente pendiente de paridad. |
| Presupuesto: campos/ítems/categorías, numeración y borrador | Implementado y verificado | SQL y navegador: alta y revisión. Descuento, impuesto, anticipo, condiciones y ARS/USD. |
| Cálculos exactos, límites y redondeo | Implementado y verificado | PostgreSQL numeric/bigint en servidor, BigInt en cliente; pruebas de cantidad/precio inválidos, descuento, límite, redondeo. No float monetario. |
| Versiones inmutables, enlaces con hash, revocación/sustitución/vencimiento | Implementado y verificado | Pruebas SQL y navegador; token en fragmento URL, RPC sin hash público. No prueba de logs del proveedor real. |
| Decisiones públicas explícitas, sin cuenta del cliente, sin GET mutador | Implementado y verificado | RPC de lectura sin decisión no cambia; SQL rechaza duplicada/no confirmada/vencida; navegador acepta en sesión independiente. No firma certificada. |
| Duplicación de borrador | Implementado, pendiente de verificar | Rama RPC/UI portada; no recorrido específico automatizado de duplicación. |
| Trabajo manual y desde aceptación, estados, lista/tablero e historial | Implementado y verificado | Conversión SQL idempotente y recorrido de navegador; importe/alcance conservados. Partes manuales probadas por reglas SQL; no todo tablero solo con teclado. |
| Cambio de importe con motivo/confirmación y límite por cobrado | Implementado, pendiente de verificar | Regla SQL portada; falta ampliar caso específico del cambio manual en nueva suite. |
| Cobros parciales/reintentos/saldo/anulación sin borrar | Implementado y verificado | SQL de pagos duplicados, discrepantes, moneda, sobrepago y anulación; navegador total 1.500,50, cobro 500 y saldo 1.000,50. No procesamiento real de dinero. |
| Panel: pendientes/acciones/entregas/terminados con saldo/cobros por moneda | Implementado y verificado | Panel con datos propios en prueba local. No estadísticas decorativas, ganancia ni conversión monetaria. |
| WhatsApp editable/normalización de teléfono | Parcial | Mensaje generado y enlace oficial para elegir destinatario; no editor dedicado ni envío directo al teléfono normalizado como en Laravel. No se envió WhatsApp. |
| Exportaciones propias y CSV seguro | Parcial | RLS de registros y helper CSV probado. Formato actual JSON por fila; descarga completa de todos los tipos no probada en navegador, tope 1.000 registros de la colección cargada. No importación ni backup completo. |
| Eventos mínimos separados de auditoría, demos excluidas | Implementado y verificado | SQL privado crea `metric` separado de `activity`, primeros hitos deduplicados y exclusión demo; sin proveedor externo de analítica. Validación de activación/comercial pendiente. |
| Autorización server-side y RLS/IDs | Implementado y verificado | SQL real con dos propietarios, SELECT filtrado, mutaciones ajenas rechazadas y permisos directos denegados. No claves privilegiadas en cliente. No auditoría externa. |
| XSS, secretos, sesiones y límites | Parcial | React escapa texto, headers seguridad, build rechaza claves privilegiadas reconocibles; Auth gestionado por Supabase. Límites RPC por UID/huella y token, sin garantía de protección IP/CDN ni ensayo externo. Sesión en navegador; CSP completa, carga/seguridad integral pendientes. |
| Progreso inicial real, omitible y retomable | Pendiente | Configuración inicial del negocio existe, pero no guía de pasos/progreso. |
| Conservar formulario ante validación y guardado visible | Implementado y verificado | Error de feedback conserva campos; Formularios de presupuesto conservan estado; confirmación de guardado manual. No autosave ni recuperación tras cerrar/crash. |
| Advertencia al salir y conflictos de edición | Parcial | `beforeunload` para formularios sucios; revisión esperada en RPC de presupuesto/trabajo probada SQL. No merge automático, ni protección de todo campo de cliente/configuración. |
| Errores de red/reintento sin duplicación general | Parcial | Mensajes y reintento de carga; idempotencia de cobros/conversión/decisión. No idempotencia universal de nuevas altas ni garantía tras recargar formulario de cobro. |
| Búsqueda/filtros/fechas/paginación y limpiar | Parcial | Tres listas con filtros y páginas de 20 sobre máximo 1.000 cargados; no paginación remota ni conservación al volver al detalle. Fechas se refieren a creación. |
| Feedback visible con pantalla/contacto/versión, sin adjuntos | Implementado y verificado | SQL/RPC y envío público con confirmación. Pantalla por categoría, no URL/token ni formulario completo. |
| Bandeja privada de producto con estados | Pendiente | Sin administrador web, estados ni suplantación. Consulta solo por operador autorizado en SQL Editor. |
| Perfil/contraseña/recuperación/baja confirmada | Pendiente | No prometer que el SMTP o el proveedor de Auth por sí solos completan flujos que no están en la interfaz. |
| Cuenta persistente y limpieza documentada | Parcial | PostgreSQL persiste fuera de Vercel; demo expira y poda SQL probadas. Cron alojado/backup/restauración/reinicio real pendientes. No limpieza automática de cuentas registradas. |
| Explicación de borrado/respaldos y políticas legales | Parcial | Retención documentada, sin borrado inmediato prometido; procedimiento de baja y revisión legal pendientes, sin certificación normativa. |
| Salud sin datos | Implementado y verificado | `/up` responde estado/versión; no verifica DB/cron. |
| Errores investigables sin secretos | Parcial | Supabase/Vercel aportan logs externos; cliente muestra mensajes sin captura de formularios. No se portó el logger PHP ni se configuraron alertas/monitorización de producción. |
| Cron/colas/backup/restauración/rollback | Parcial | Función de poda SQL probada, pasos documentados. Programación cron y recuperación del proveedor no ensayadas; no worker de aplicación. Revertir código no revierte SQL ni vuelve Laravel compatible con Vercel. |
| URL HTTPS/persistencia/servicio independiente de PC | Parcial | Dominio HTTPS público y recorrido completo verificados el 2026-11-01; recarga conserva sesión/negocio. Falta reinicio/redeploy, disponibilidad continua, cuotas y operación del proveedor. |
| PWA: id/standalone/maskable/instalar/instrucciones/instalación real | Pendiente | Manifest/iconos previos no completan PWA. Sigue `display: browser`, sin `/instalar` ni service worker/offline. No APK. |
| Móvil/escritorio, errores/vacíos/carga, contraste y teclado | Parcial | Pruebas actuales y capturas 360×800/1440×1000; sin desborde en rutas públicas y trabajo. Revisión de contraste de marca conservada. Recorrido completo exclusivamente con teclado, Safari/Firefox/dispositivos y certificación pendientes. |
| README, changelog, material editorial, guías/limitaciones reales | Implementado y verificado | Documentación actual separada de históricos; no publicaciones, fechas históricas, métricas ni revisiones humanas inventadas. |
| IA/fiscal/pagos/suscripciones/inventario/nómina/contabilidad/marketplace/chat/nativas/offline/automatización WhatsApp | Fuera de la primera beta | No se incorporan como parte de la migración. |

## Evidencia y siguiente paso

[Verificación actual](VERIFICACION_MIGRACION.md), [guion de diez minutos](TESTERS.md), [operación/feedback](OPERACION.md) y [paso a paso de publicación](DESPLIEGUE_SUPABASE_VERCEL.md).

La conexión Vercel/Supabase y el alta anónima ya permitieron verificar el recorrido público. [Resultados públicos](VERIFICACION_MIGRACION.md#verificación-pública--2026-11-01). Siguen pendientes correo/Google, PWA y operación/recuperación del proveedor, además de las funciones marcadas arriba. No pedir ni publicar claves privadas en chat; no declarar todo el alcance completo.
