# Alcance y reglas de TrabajoClaro

## Recorrido

Cada cuenta crea un negocio y su membresía propietaria. Todos los accesos privados pasan por autenticación y contexto de negocio; las consultas por ID y exportaciones agregan `business_id`. Las claves foráneas compuestas impiden referencias entre negocios en la base. No hay invitaciones ni interfaz de cambio de negocio. Usar nombres iguales en negocios distintos no comparte datos.

Cliente → presupuesto/borrador → versión publicada → decisión → trabajo → cobros → cierre.

Los clientes se archivan, no se borran. Su historial se conserva; los clientes archivados no pueden usarse para nuevas propuestas/trabajos. Los trabajos históricos pueden seguir actualizándose.

## Dinero y cantidades

ARS y USD; un documento y todos sus cobros tienen una sola moneda. No hay conversión ni sumas de monedas distintas. Los resúmenes agrupan por moneda. Se aceptan cadenas decimales con punto, sin separadores, signos ni notación exponencial: cantidades con hasta 3 decimales y precios/importes con hasta 2. Cantidades > 0; precios >= 0. Los documentos sin cargo son posibles, los cobros de cero no.

Se usan BCMath y centavos enteros, nunca `float` para calcular. Por cada ítem: cantidad × precio en centavos, redondeado a centavo con mitad hacia arriba. Se suman los ítems redondeados, se resta un descuento **fijo** y se aplica un impuesto porcentual (0–100) sobre esa base, redondeado del mismo modo. El descuento no supera el subtotal; el anticipo solicitado no supera el total. Máximo por documento: 999.999.999,99. Solicitar anticipo no registra un cobro.

Ejemplo: 0.005 × 1.00 = 0.005 → 0.01; más 3 × 0.10 = 0.30; subtotal 0.31; descuento 0.01; impuesto 5% de 0.30 = 0.015 → 0.02; total 0.32. La suite verifica este caso.

## Presupuestos y versiones

Numeración correlativa por negocio bajo bloqueo transaccional. Guardar modifica solo el borrador. Publicar copia datos públicos del negocio/cliente, moneda, alcance, exclusiones, ítems, importes, fechas y condiciones. Editar un cliente, negocio o borrador no modifica esa copia. Las notas internas, teléfono y correo no se muestran en el documento público.

Una nueva publicación revoca todos los enlaces anteriores del mismo presupuesto; se conservan documentos y decisiones. Un presupuesto aceptado queda protegido de edición/publicación: se puede duplicar para otra propuesta. Duplicar crea un nuevo número/borrador y actualiza fechas. Un trabajo conserva el alcance y total de la versión **aceptada**, no un borrador posterior.

Los tokens tienen 256 bits aleatorios. Se guarda SHA-256; el enlace completo se muestra una vez en sesión cifrada tras publicar. Si se pierde, publicar otra versión revoca la anterior. El propietario puede revocar explícitamente cualquier enlace sin borrar decisiones. La vista interna permite imprimir versiones históricas. El PDF usa la función Imprimir/Guardar PDF del navegador.

El vencimiento es inclusivo hasta el final del día indicado, en la zona horaria guardada en la versión. Los estados vencidos se calculan al consultar; no hace falta un proceso de actualización. El listado principal identifica el estado del presupuesto; cada versión muestra su propio estado/vencimiento. Los borradores no son prueba de envío. No se registran aperturas como lectura humana.

## Decisiones

Un visitante con el token puede aceptar, rechazar o solicitar una modificación. Se exige nombre, elección y casilla de confirmación. El POST usa CSRF, límites de solicitudes y transacción con bloqueo en el mismo orden que publicar/revocar. Un GET nunca decide. Solo una decisión por versión (índice único). No se permiten nuevas decisiones si venció, se revocó, se sustituyó o ya fue respondida. Solicitar modificación no es aceptar; permite preparar y publicar una nueva versión.

El nombre declarado no verifica identidad. No es firma digital certificada ni garantiza validez jurídica. El enlace es una capacidad privada: quien lo posea puede ver/responder. Compartirlo sigue siendo responsabilidad del propietario; no ponerlo en analítica, logs, referencias ni buscadores. Los encabezados y robots excluyen los documentos, pero un token no debe publicarse abiertamente.

## Trabajos y cobros

Convertir es idempotente: bloqueo de presupuesto y `quote_id` único en trabajos. Crear manualmente requiere cliente, alcance e importe. Estados: pendiente, en curso, en espera, terminado y cancelado. Estado de trabajo y saldo son independientes; cancelar o terminar no elimina ni registra pagos automáticamente.

El cliente y moneda de un trabajo existente se conservan. Cambiar el importe exige confirmación del propietario y motivo, queda con importe anterior/nuevo en historial, y no puede reducirse por debajo de lo ya cobrado. Los cambios de alcance, fechas, próxima acción y estado también quedan documentados. El presupuesto aceptado original nunca se altera por ese cambio.

Cada cobro deriva cliente/negocio/moneda del trabajo en servidor. Se bloquea el trabajo antes de verificar saldo e insertar. Importe positivo, sin superar saldo, fecha no futura en zona del negocio. La clave de reintento evita duplicados; si se reutiliza con datos distintos se rechaza. Un cobro se corrige anulando con motivo y creando otro con nueva clave, nunca borrando; el saldo vuelve a incluir el importe anulado. Reintentar el mismo cobro anulado devuelve el movimiento original, no lo reactiva.

**Registro interno de cobros. No es un comprobante fiscal.** No se almacenan datos de tarjetas ni se procesan pagos. Lo cobrado no se presenta como ganancia; lo aceptado no se presenta como ingreso cobrado.

## Panel, demo y exportaciones

El panel muestra versiones pendientes vigentes, acciones vencidas de trabajos activos, entregas hasta siete días (incluye atrasadas), trabajos terminados con saldo, y cobros vigentes desde el inicio del mes hasta hoy, separados por moneda.

Cada inicio de demo crea usuario, negocio y datos ficticios independientes con vencimiento de 24 horas. No hay cuenta compartida de visitantes. Las cuentas no son recuperables por email y no envían mensajes ni correos. El negocio del seeder es distinto de las demos anónimas y no vence automáticamente; solo existe para desarrollo local. `demo:prune` elimina demos anónimas vencidas; no borra negocios reales.

CSV con autorización, ámbito por negocio, UTF-8 BOM, escapado CSV y prefijo seguro ante fórmulas (=,+,-,@, controles y espacios iniciales). Se exportan clientes, borradores y versiones en JSON dentro de columnas CSV, trabajos y movimientos incluidos los anulados. Nunca tokens/hash. Para conservar privacidad, descargá en un equipo autorizado. El CSV es portabilidad, no reemplaza un backup de base.

WhatsApp abre un enlace oficial wa.me con número internacional normalizado y texto editable codificado. No adivinamos códigos de país; se exige +código y se remueven espacios/guiones/paréntesis. El usuario decide si envía. Ninguna apertura genera estado de enviado/recibido. En demo no se incluyen números reales por defecto.

## Seguridad, operación y límites

Laravel proporciona sesiones, hash de contraseñas, CSRF, validación y consultas ORM parametrizadas; el middleware aplica autorización por negocio. Las respuestas privadas no se cachean; CSP, escape Blade, referrer y bloqueo de frames reducen exposición. Las credenciales de base se configuran en `.env`. No se necesitan servicios externos. El rate limiter usa caché compartida en MySQL y debe conservarse compartida si se escalan procesos.

No hay recuperación de contraseña, invitaciones, firma certificada, adjuntos, facturación ARCA, pagos/suscripciones, inventario, contabilidad, nómina, marketplace, chat, IA, apps nativas, offline ni automatización de WhatsApp. La traducción usa el mecanismo de Laravel con archivos `lang/es`; la beta está escrita en español y no ofrece selector de idioma.

Las solicitudes de piloto se almacenan con validación, CSRF, honeypot y límite por IP. No hay panel CRM de solicitudes ni envío automático: consultar con acceso administrativo autorizado a la base. Antes de lanzamiento real faltan revisión legal/privacidad, acuerdo de retención y procedimiento de atención/recuperación de cuentas. No se afirma cumplimiento normativo automático. La beta está pensada para un piloto acompañado, no para abrir indiscriminadamente a producción.
