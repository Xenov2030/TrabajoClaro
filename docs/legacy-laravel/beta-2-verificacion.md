> Histórico de Laravel/MySQL. No acredita el estado de Next.js/Supabase. [Estado actual](../ESTADO_BETA.md).

# Ampliación beta 2 · verificación y entrega

Versión: **2026.10-beta.2**. Fecha de revisión: 31/10/2026 (Argentina).

## Estado del acceso público

**URL pública: no disponible.** No hay un servicio de hosting/preview conectado, credenciales de despliegue ni un dominio autorizado configurados. Se comprobaron las herramientas disponibles, la configuración administrada y los nombres de variables/identidades, sin leer ni mostrar claves. Tener Docker en la tarea no es un destino público permanente.

Falta elegir/conectar un hosting existente compatible y aportar su dominio/subdominio o hostname HTTPS. No se contrató un plan, no se modificaron sitios existentes y no se inventó una URL. Vercel es una plataforma de despliegue, pero este Laravel/MySQL está preparado directamente para hosting PHP administrado o contenedores; usar Vercel requiere adaptaciones y una base externa. Ver `despliegue.md` para comparar opciones.

La fecha de vencimiento del enlace, cuotas, suspensión por inactividad y disponibilidad no se pueden determinar hasta conocer el proveedor/plan. No se afirmó que el entorno de tarea sea un hosting independiente con permanencia garantizada.

## Cambios implementados sin reiniciar el proyecto

- Identidad existente conservada y refinada: verde profundo, fondo claro, paleta/espaciado documentados, controles con mejor contraste, foco visible e íconos SVG.
- Pantallas de 360 px sin tabla incómoda en el presupuesto público: tarjetas por ítem, tabla solo en escritorio e impresión; navegación completa y ajuste de textos largos.
- Estado de envío con botón deshabilitado, `aria-busy`, texto y spinner; confirmaciones, vacíos y errores con acciones para continuar. Las respuestas 429 indican minutos aproximados de espera.
- Accesos **Probar demo** y **Crear cuenta de prueba**, sin credenciales administrativas en la landing. Se conserva el aislamiento existente.
- Botón de feedback en el aviso de beta; enlaces en el pie y errores. Guarda pantalla, descripción, contacto opcional y versión. La pantalla se identifica por una clave permitida, nunca por el enlace/token privado. La pertenencia deriva de sesión, no de IDs enviados por el navegador.
- Identificación visible de versión y recomendación de usar datos ficticios; página `/beta` con explicación y limpieza.
- Feedback con validación, CSRF, honeypot y límite separado por IP; sin consulta pública de comentarios ni correos automáticos.
- Retención: demo anónima 24 h más siguiente limpieza horaria; feedback 90 días más siguiente limpieza diaria; cuentas registradas sin borrado automático por inactividad durante el piloto. Una limpieza/cierre de cuentas requiere aviso previo de al menos 7 días y posibilidad de exportar. El scheduler quedó configurado también para el entorno local.
- Imagen de producción con Apache/PHP y dependencias sin desarrollo, perfil de MySQL privado/volúmenes, scheduler, proxy Caddy HTTPS, secretos por entorno y migraciones explícitas.
- Logs de aplicación con contexto seguro; Apache sin URI/referrer/cookies; Caddy con request filtrado. Secretos y claves fuera del repositorio e imagen.
- Script de backup y procedimientos de recuperación; pruebas con datos ficticios en una base separada.

## Evidencia local

| Comprobación | Resultado |
| --- | --- |
| Suite SQLite | **34 pruebas aprobadas, 310 comprobaciones** |
| Misma suite en MySQL | **34 pruebas aprobadas, 310 comprobaciones** |
| Chromium, desarrollo | **6 pruebas aprobadas**, 360×800 y 1440×1000 |
| Chromium, imagen de producción local | **6 pruebas aprobadas**, mismos tamaños y backend completo |
| Compilación Vite | Aprobada |
| Pint | Aprobado, 57 archivos PHP |
| Imagen de producción | Construida; dependencias de desarrollo excluidas |
| Caddy | Configuración validada; no se inició la emisión de certificado para un dominio ficticio |
| Configuración en contenedor | Producción, debug off, cookies seguras, sesiones cifradas, MySQL y ausencia de `.env` comprobados |
| Puerto público de MySQL | Ninguno (`PortBindings` vacío) |
| Reinicio de app/base/scheduler | Recuentos y huella de datos conservados |
| Backup/restauración | Dump comprimido restaurado en base nueva; coinciden los recuentos de usuarios, trabajos, cobros y feedback |

La suite funcional conserva aislamiento entre dos negocios, totales/redondeos, versiones, decisiones, conversión idempotente, cobros/anulaciones/saldo, monedas y CSV autorizado. Agrega persistencia/autoría de feedback, validación, retención, limpieza de demos sin romper feedback, no exposición de tokens en la identificación de pantalla, logging seguro y ausencia de credenciales compartidas en la landing.

Los seis recorridos de navegador combinan:

1. Feedback: pantalla preseleccionada, comentario, contacto vacío permitido, estado de carga visible y confirmación de almacenamiento.
2. Registro independiente: negocio nuevo, logout e ingreso con su propia cuenta.
3. Demo: presupuesto con varios ítems, publicación, cliente en sesión independiente, aceptación, trabajo, pago parcial, saldo y terminación. Incluye generación PDF y ocultamiento de controles al imprimir.

Para revisar el estado transitorio de envío se retiene la navegación nativa en la prueba, se captura la carga y luego se continúa el POST real. No se reemplaza el endpoint por una respuesta ficticia.

Se revisaron capturas de landing, panel, editor, presupuesto público y feedback (carga/confirmación) en móvil y escritorio. Los recorridos comprueban el ancho del documento y errores JS. Se corrigieron la navegación móvil cortada, la tabla pequeña del cliente, contraste de controles, asteriscos de etiquetas y ajuste de encabezados. Paleta y resultados de contraste en `diseno.md`.

Durante las reconstrucciones se agotó el espacio de Docker. Se liberaron cachés, imágenes sin uso y metadatos Git de dependencias instaladas; no se eliminaron el repositorio, sus fuentes, cuentas, bases ni volúmenes. Las suites se volvieron a ejecutar después de esa limpieza. El contenido instalado de las dependencias y los lockfiles se conservaron.

## Lo que no se pudo comprobar públicamente

| Requisito publicado | Estado |
| --- | --- |
| Landing HTTPS desde otra red/dispositivo | Pendiente: falta hosting/dominio conectado |
| Demo y cuenta propia desde URL pública | Pendiente; comprobadas localmente |
| Publicación/aceptación/trabajo/pago/saldo públicos | Pendiente; comprobados contra backend e imagen de producción locales |
| Feedback y aislamiento entre testers en destino | Pendiente; comprobados en pruebas locales |
| Certificado y redirección HTTPS reales | Configuración validada; emisión y acceso externo pendientes |
| Persistencia, respaldos y recuperación del proveedor | Ensayo local aprobado; falta verificar el servicio real |
| Vencimiento/inactividad/cuotas | Desconocidos hasta elegir proveedor y plan |

Tampoco se revisaron dispositivos físicos, Safari/Firefox, carga concurrente ni políticas legales profesionales. No hay recuperación de contraseña ni envío de correos; no se simula entrega. No se envió WhatsApp.

## Siguiente acción concreta

Conectar un servicio existente compatible con PHP 8.4/MySQL o contenedores y definir un dominio/subdominio para **una beta aislada**. El acceso se configura por el mecanismo seguro del proveedor/entorno, nunca pegando claves en chat. Si requiere contratar algo, primero autorizar el costo. Con ese destino se puede aplicar la preparación descrita en `despliegue.md` y ejecutar las verificaciones desde su URL HTTPS exacta.
