> Histórico de Laravel/MySQL. No acredita el estado de Next.js/Supabase. [Estado actual](../ESTADO_BETA.md).

# Prueba de primera beta

**URL pública: todavía no disponible. PWA: pendiente, usar navegador normal con conexión.** El [guion de diez minutos, restricciones y estado consolidado](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/docs/ESTADO_BETA.md#guion-exacto-de-diez-minutos) describe la entrega actual y los pasos exactos. Las instrucciones siguientes conservan la demostración breve y las preguntas de validación.

# Demo de cinco minutos

Usá “Probar demo”: los datos son ficticios y cada visitante tiene su espacio. No ingreses contactos reales sin autorización.

1. **0:00–0:45 — Problema.** Preguntá cómo saben hoy qué aprobó un cliente, qué sigue y cuánto falta cobrar. Mostrá “Qué requiere atención” sin prometer ahorros.
2. **0:45–1:30 — Cliente y propuesta.** Abrí Marina o creá un cliente de ejemplo. Prepará un presupuesto corto con alcance, exclusión, ítem, moneda y vencimiento. Guardá; explicá el total calculado por el servidor.
3. **1:30–2:30 — Versión y decisión.** Publicá y copiá el enlace una sola vez. Abrilo en ventana privada para representar al cliente. Mostrá alcance, condiciones, imprimir/PDF y confirmar con nombre. Aclará que no es firma certificada. Aceptá. Publicar no significa enviar.
4. **2:30–3:30 — Trabajo.** Volvé al negocio, abrí el presupuesto y creá su trabajo. Repetí el botón para mostrar que no duplica. Definí fecha y próxima acción; cambiá el estado a en curso.
5. **3:30–4:20 — Cobro.** Registrá un anticipo ficticio. Mostrá saldo y diferencia entre terminar/cobrar. Mostrá cómo anular con motivo, sin borrar el historial.
6. **4:20–5:00 — Portabilidad y siguiente paso.** Descargá CSV desde Configuración. Preguntá qué parte usarían con su próximo encargo y qué les impediría hacerlo. No propongas un precio ficticio ni prometas funcionalidades pendientes.

# Preguntas para validar

- Contame sobre el último trabajo cuyo seguimiento te resultó difícil. ¿Qué pasó y cómo lo resolviste?
- ¿Cuántos trabajos seguís al mismo tiempo? ¿Quién actualiza esa información?
- Mostrame, si te parece, cómo encontrás el alcance aprobado y los pagos de un trabajo. ¿Qué herramientas te funcionan bien?
- ¿En qué situaciones necesitás confirmar cambios de alcance o precio? ¿Qué información te falta después?
- ¿Cómo diferenciás un trabajo terminado de uno completamente cobrado?
- ¿Qué parte de este recorrido te resultó confusa o innecesaria?
- ¿Qué necesitarías conservar o exportar para probarlo sin abandonar tus herramientas actuales?
- ¿Lo usarías en un próximo encargo concreto? ¿Cuál y quién lo cargaría?
- ¿Pagás hoy por alguna herramienta o por organizar esta información? ¿Cómo decidiste ese gasto?
- Si después de probarlo te sirve, ¿qué condiciones necesitarías para contratar un piloto pago? ¿Quién toma esa decisión y qué presupuesto consideran razonable?
- ¿Qué te haría dejar de usarlo después de una semana?

Evitá inducir respuestas del tipo “¿te ahorraría mucho tiempo?”. Registrá citas con consentimiento y ejemplos observables; no infieras intención de pago a partir de elogios.

# Medición interna

`product_events` está separado de `activity_events` (auditoría). No se envía a proveedores externos y no guarda textos de clientes ni tokens. Demos y el negocio del seeder quedan excluidos.

- **Activación:** negocios registrados que crean su primer presupuesto, publican una versión y eventualmente registran aceptación/trabajo/cobro. Comparar cohortes por fecha real de registro; documentar numerador, denominador y ventana antes de calcular tasas.
- **Uso repetido:** analizar trabajos creados, decisiones aceptadas y cobros en días/semanas distintos; complementar con fechas de documentos y movimientos. Los eventos “first_*” se registran una sola vez por negocio; los eventos recurrentes de trabajo/aceptación se conservan por ocurrencia.
- **Piloto pago:** todavía no hay suscripciones ni pago integrado. Llevar un registro externo explícito de participantes contactados, prueba realizada, propuesta comercial real, aceptación y pago efectivamente confirmado. No equiparar una solicitud de formulario con una conversión paga.

No hay tasas ni mercado validado antes de observar uso real. Para evaluar el piloto acordá una ventana de prueba, entrevistá a quienes abandonan y compará utilidad con sus herramientas actuales. No uses demo ni visitas automáticas como actividad humana real.
