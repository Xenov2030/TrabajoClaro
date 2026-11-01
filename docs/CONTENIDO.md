# TrabajoClaro: base factual para crear contenido

Última revisión: **2026-10-31**, zona `America/Buenos_Aires`. Autor declarado: **Nicolas Aguirres**. [Portfolio](https://nicolasaguirrescode.com/).

Este documento sirve de referencia para LinkedIn, Instagram, TikTok, YouTube y el portfolio. **No contiene publicaciones, testimonios ni una historia comercial inventada.** Consultar primero el [README](../README.md), el código actual y los resultados de verificación. El identificador `2026.10-beta.2` es una configuración de la beta, no prueba de un release público.

## Qué sabemos y qué no

| Afirmación o tema | Estado de la evidencia | Uso responsable |
| --- | --- | --- |
| La aplicación sigue cliente → presupuesto → decisión → trabajo → cobro | Implementado; rutas, servicios y pruebas del recorrido | Se puede demostrar con un encargo ficticio y aclarando que el cobro es un registro manual. |
| Público objetivo: pequeños negocios de servicios/fabricación por encargo | Definido en la consigna del producto | Describir como público al que se dirige, no como clientes que ya lo usan. |
| Seguir acuerdos entre chats, papel y planillas puede ser difícil | Hipótesis que motivó el producto | No atribuirla a entrevistas o experiencia personal del autor sin confirmación. |
| Todos esos negocios necesitan cambiar sus herramientas | Sin evidencia; no es una premisa del producto | No afirmarlo. Sus herramientas actuales pueden alcanzarles. |
| Hay demanda, uso repetido o disposición a pagar | Pendiente de validar | No decir que el mercado está validado, que hay clientes ni que alguien pagó. |
| Hay beta funcional | Código y suites locales | Explicar el alcance; no presentarla como versión estable o servicio público disponible. |
| Hay una URL pública HTTPS para testers | No disponible/verificada | No inventar enlaces, CTA a un sitio inexistente ni usar loopback como demo pública. |
| Se ahorra tiempo, se recuperan ventas o crece la ganancia | Sin mediciones ni resultados acreditados | No publicar cifras ni promesas económicas. |
| El sistema es seguro o cumple normas de forma certificada | Hay controles y pruebas locales, no certificación/auditoría independiente | Describir controles concretos y límites; no garantizar invulnerabilidad, cumplimiento legal ni identidad del firmante. |

La motivación documentada es **explorar** si reunir alcance, próximas acciones y saldos ayuda a esos negocios. No hay un relato confirmado del tipo “me pasó con un cliente”, “entrevisté a varios talleres” o “detecté un problema en el mercado”. Esos relatos requieren evidencia adicional del autor.

## Funciones que se pueden mostrar

| Hecho demostrable | Fuente principal | Límite que debe acompañarlo |
| --- | --- | --- |
| Registro de negocio propietario y separación de datos | [AuthController](../app/Http/Controllers/AuthController.php), [BusinessContext](../app/Http/Middleware/BusinessContext.php), [WorkflowTest](../tests/Feature/WorkflowTest.php) | Una cuenta/negocio propietario; sin invitaciones ni roles avanzados. |
| Cliente, presupuesto con ítems y cálculo en servidor | [AppController](../app/Http/Controllers/AppController.php), [Money](../app/Services/Money.php) | Descuento fijo e impuesto porcentual; no cálculo contable/fiscal completo. |
| Publicar conserva los datos de esa versión | [Workflow](../app/Services/Workflow.php), [pruebas de versiones](../tests/Feature/WorkflowTest.php) | Editar borrador no edita lo publicado; una publicación nueva revoca los enlaces anteriores. |
| Un visitante responde sin crear cuenta | [PublicQuoteController](../app/Http/Controllers/PublicQuoteController.php), [documento](../resources/views/quotes/document.blade.php) | Requiere enlace privado, nombre y confirmación; no verifica identidad ni constituye firma digital certificada. |
| Convertir lo aceptado a trabajo conserva alcance e importe | [Workflow](../app/Services/Workflow.php) | Conversión idempotente; cambios posteriores de importe tienen confirmación y motivo. |
| Lista y tablero de trabajos con próxima acción | [vista de trabajos](../resources/views/jobs/index.blade.php), [AppController](../app/Http/Controllers/AppController.php) | Cambiar estados desde formulario; no hay arrastre de tarjetas. |
| Registrar cobro parcial muestra saldo y admite anulación trazable | [Workflow](../app/Services/Workflow.php), [Job](../app/Models/Job.php) | No mueve dinero, no valida que una transferencia ocurrió y no emite comprobante fiscal. |
| Resúmenes separados en ARS y USD | [panel](../resources/views/dashboard.blade.php), [AppController](../app/Http/Controllers/AppController.php) | No convierte monedas; lo cobrado no es ganancia. |
| WhatsApp con texto editable | [detalle del presupuesto](../resources/views/quotes/show.blade.php), [JavaScript](../resources/js/app.js) | El usuario decide si envía; abrirlo no acredita entrega ni lectura. |
| Portabilidad por CSV con control de pertenencia | [ExportController](../app/Http/Controllers/ExportController.php), [Csv](../app/Services/Csv.php) | No es un respaldo/restauración de toda la base. |
| Demo aislada y feedback guardado | [Demo](../app/Services/Demo.php), [FeedbackController](../app/Http/Controllers/FeedbackController.php), [BetaFeedbackTest](../tests/Feature/BetaFeedbackTest.php) | Datos ficticios; demo de 24 h y feedback de 90 días más sus limpiezas programadas. |

La [guía de capturas](GUIA_CAPTURAS.md) concreta cómo demostrar estas funciones sin exponer información sensible.

## Decisiones técnicas y motivos respaldados

| Decisión | Motivo documentable | Evidencia / alcance |
| --- | --- | --- |
| Monolito Laravel, Blade y MySQL | Mantener el recorrido en una sola aplicación, con herramientas del framework para sesiones, validación, CSRF y ORM | [composer.json](../composer.json), [rutas](../routes/web.php), [migraciones](../database/migrations). No se comparó rendimiento con una SPA. |
| Tailwind, Vite y JavaScript progresivo | Compilar recursos locales y sumar ítems, carga e impresión sin una SPA | [package.json](../package.json), [resources](../resources), [diseño](diseno.md). No usa Alpine.js. |
| BCMath y centavos enteros | Definir cálculo/redondeo sin `float` en dinero | [Money](../app/Services/Money.php), pruebas de cantidades, impuestos y límites. No demuestra exactitud contable de una empresa. |
| Copias de versiones publicadas | Mantener la propuesta que recibió una decisión aunque cambie un cliente o borrador | [Workflow](../app/Services/Workflow.php), modelo [QuoteVersion](../app/Models/QuoteVersion.php). |
| Transacciones, bloqueos e índices únicos | Proteger numeración, decisiones, conversión y cobros frente a operaciones repetidas | [migraciones](../database/migrations), [Workflow](../app/Services/Workflow.php). No se hizo una prueba de carga concurrente entre procesos. |
| Contexto del negocio derivado de la sesión | Evitar que un ID o un campo enviado por el cliente elija el negocio ajeno | [BusinessContext](../app/Http/Middleware/BusinessContext.php), pruebas de IDs y exportación. |
| Demo separada por visitante | Permitir acciones reales sin un administrador/negocio compartido | [Demo](../app/Services/Demo.php), limpieza en [console.php](../routes/console.php). |
| Auditoría separada de eventos de producto | Diferenciar cambios operativos de señales mínimas de uso; excluir demos | [Workflow](../app/Services/Workflow.php). Tener eventos no demuestra uso real ni resultados. |
| Contenedores y perfil de producción persistente | Ejecutar PHP/MySQL donde no había PHP nativo y preparar la operación completa | [Docker local](../docker/Dockerfile), [producción](../docker/production.Dockerfile), [Compose](../compose.production.yaml). Sigue faltando un proveedor público conectado. |

No afirmar que estas decisiones son “las mejores” o que permiten cierta cantidad de usuarios sin una evaluación que lo respalde.

## Desafíos reales registrados

Los siguientes hechos constan en los [informes inicial](verificacion.md) y [beta 2](beta-2-verificacion.md), el código corregido y/o el registro de esta sesión. No son incidentes sufridos por clientes en producción.

| Desafío | Corrección observada | Evidencia / cómo contarlo |
| --- | --- | --- |
| El entorno inicial no tenía PHP y no permitía instalar paquetes del sistema con ese usuario | Runtime Docker para PHP y MySQL | Describir una preparación del entorno de desarrollo, no una migración de clientes. |
| La API de GitHub estaba bloqueada para descargar dependencias | Composer por Git HTTPS con la CA/proxy existentes y TLS activo | [instrucciones de nube](../scripts/cloud-install.sh); no se desactivó verificación para hacer pasar la instalación. |
| Cierre contiguo de directivas Blade y confirmación de importe exigida sin cambio | Separación de directivas y validación opcional/condicional correcta | Informes y [AppController](../app/Http/Controllers/AppController.php); suites posteriores aprobadas. |
| MySQL ordena las claves de JSON al persistir | Comparación estricta del documento persistido antes/después en la prueba de inmutabilidad | [WorkflowTest](../tests/Feature/WorkflowTest.php); no se relajó el requisito de conservar datos. |
| Límites numéricos de solicitudes compartían clave entre acciones | Prefijos independientes para demo, ingreso, feedback, enlaces y otras rutas | [web.php](../routes/web.php) y prueba de regresión. |
| Navegación móvil cortada y tabla pequeña en el presupuesto del cliente | Navegación completa y tarjetas de ítems en móvil, tabla en escritorio/PDF | [estilos](../resources/css/app.css), [documento](../resources/views/quotes/document.blade.php), capturas de beta 2. |
| Era difícil observar la carga durante navegación nativa en el test | El test retiene la navegación, revisa el estado y luego continúa el POST real | [beta.spec.js](../tests/Browser/beta.spec.js); no se presenta una respuesta simulada como backend funcionando. |
| Se agotó el disco durante reconstrucciones de Docker | Limpieza de cachés, imágenes sin uso y metadatos Git de dependencias; repetición de verificaciones | Informe beta 2. No se borraron los volúmenes para hacer pasar las pruebas. |
| No había destino de hosting conectado | Preparación y ensayo local de producción; publicación declarada pendiente | [despliegue](despliegue.md). No hay una URL pública que pueda mostrarse como resultado. |

## Diferencias verificables frente a llevar información manualmente

Una planilla bien diseñada también puede calcular, registrar historial o evitar duplicaciones. No hay un estudio comparativo con el proceso de un negocio real. Lo que sí puede afirmarse es qué hace esta aplicación al ejecutar el recorrido:

| Operación | Comportamiento verificable en TrabajoClaro | Lo que no demuestra |
| --- | --- | --- |
| Reutilizar un acuerdo aceptado | Crea el trabajo desde la versión aceptada sin volver a cargar alcance/importe | Cuántos minutos ahorra frente a la herramienta actual de cada persona. |
| Consultar qué se aceptó | Conserva versión y decisión asociada | Que WhatsApp o papel siempre pierden acuerdos; tampoco validez jurídica garantizada. |
| Consultar el saldo | Resta cobros vigentes, conserva anulaciones y separa monedas | Que el registro coincida con el banco ni que aumente la cobranza. |
| Ver el siguiente paso | Reúne acciones vencidas y entregas de los trabajos cargados | Que priorice automáticamente el negocio o elimine errores humanos. |
| Compartir una propuesta | Genera una versión privada y un formulario de decisión | Que se haya enviado, que alguien la leyó o que consiga clientes. |
| Exportar información | Entrega CSV de las entidades del negocio autorizado | Que reemplace un backup integral ni una migración probada a otro producto. |

## Aprendizajes técnicos que se pueden explicar

Estas son conclusiones de diseño respaldadas por el código, **no citas personales atribuidas a Nicolas**:

- Un estado de trabajo y un estado de cobro responden preguntas distintas; por eso terminar el trabajo no pone su saldo en cero.
- Guardar un borrador, publicar una versión, enviarla y recibir una decisión son eventos distintos. La aplicación no inventa comprobantes de envío.
- “Una sola moneda por documento” exige validar también los movimientos y agrupar resúmenes; no alcanza con mostrar un símbolo.
- Los reintentos deben considerarse en la operación de negocio: volver a convertir o registrar el mismo pago no debería duplicar registros.
- Una demo editable necesita aislamiento y limpieza, no una contraseña administrativa compartida.
- Tener Docker, tests locales o un archivo para HTTPS no significa que exista un servicio público verificable.

Para presentarlas como aprendizajes en primera persona, el autor debe revisar y confirmar que representan su experiencia y comprensión.

## Aporte de Codex y revisión humana

| Participación | Qué puede acreditarse | Qué queda pendiente |
| --- | --- | --- |
| Nicolas Aguirres | Autor declarado en la consigna y atribución del producto; el usuario aportó objetivos, alcance y ampliaciones en la conversación | Confirmar qué decisiones y revisiones manuales realizó personalmente antes de atribuirle tareas concretas en contenido. |
| Codex | Según el registro de la sesión, inspeccionó el checkout inicial, generó implementación, migraciones, interfaz, pruebas y documentación; ejecutó comandos y revisó capturas | Esto no equivale a una revisión humana independiente ni a un porcentaje medido de autoría. |
| Verificación automatizada | Suites PHP y navegador disponibles; resultados locales de cada etapa diferenciados en README/informes | CI remota, cobertura porcentual, pruebas de carga y validación externa no acreditadas. |
| Revisión de esta documentación | Código/configuración inspeccionados; suites PHP, build y comandos seleccionados comprobados por Codex | Aprobación manual de Nicolas, auditoría profesional, revisión legal y validación con negocios reales no confirmadas. |

No decir “desarrollado íntegramente a mano”, “revisado personalmente línea por línea”, “auditoría completa” ni asignar porcentajes al aporte humano/IA. El Git observado contiene un commit inicial y cambios locales: no permite reconstruir una cronología completa de contribuciones.

## Cómo reutilizar esta base por canal

| Canal | Material factual posible | Condición antes de producirlo |
| --- | --- | --- |
| LinkedIn | Decisiones de dominio, estados separados, idempotencia, pruebas y límites de una beta asistida por Codex | Citar el estado local y confirmar atribuciones humanas; no afirmar tracción. |
| Instagram | Capturas del recorrido y diseño móvil con ejemplos | Usar la guía; ocultar enlaces privados y señalar “datos ficticios”. |
| TikTok | Grabación móvil corta de presupuesto → decisión → trabajo → saldo | No simular una transferencia real ni mostrar una URL pública inexistente. |
| YouTube | Demostración más extensa y explicación técnica verificable | Diferenciar aplicación local, configuración de producción y despliegue real. |
| Portfolio | Problema hipotético, público, alcance, stack, aporte de Codex, capturas y verificaciones | Mantener pendientes y enlaces actualizados; atribuir revisión manual solo cuando se confirme. |

Esto describe material disponible; no son guiones ni publicaciones generadas.

## Evidencia pendiente de obtener

1. Entrevistas o observaciones consentidas de negocios reales: problema, frecuencia, proceso actual y qué ya les funciona.
2. Pilotos, uso repetido y criterios de activación definidos antes de medir; excluir datos ficticios.
3. Propuestas comerciales y pagos efectivamente confirmados si se evalúa disposición a pagar. Un formulario no es una venta.
4. Revisión/aprobación humana del autor y registro de las tareas que efectivamente revisó.
5. Hosting real, dominio, vigencia/inactividad del plan y comprobación HTTPS desde otra red.
6. Revisión legal/privacidad, pruebas de dispositivos y demás límites indicados en README.

Actualizar esta base cuando exista evidencia nueva; no completar casilleros con suposiciones.
