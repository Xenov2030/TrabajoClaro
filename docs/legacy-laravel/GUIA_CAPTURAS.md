> Histórico de Laravel/MySQL. No acredita el estado de Next.js/Supabase. [Estado actual](../ESTADO_BETA.md).

# Guía de capturas y demostraciones de TrabajoClaro

Última revisión: **2026-10-31** (`America/Buenos_Aires`). Pantallas contrastadas con rutas y vistas existentes. Consultar [README](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/README.md) para instalación, estado y límites; [CONTENIDO](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/docs/CONTENIDO.md) para afirmaciones respaldadas.

No hay URL pública verificada. Las capturas pueden obtenerse con la instalación local; no convertir una dirección de loopback, marcador de dominio o entorno de tarea en una supuesta demo pública.

## Preparar una muestra coherente

Usar **Probar demo** para no tocar otro negocio, o registrar una cuenta propia de prueba. No usar ni mostrar credenciales del seeder, una cuenta administrativa compartida o datos reales de clientes.

Conjunto sugerido, explícitamente ficticio:

- Negocio: **Taller de prueba**; cliente: **Cliente de ejemplo**. Dejar teléfono, correo, dirección y notas personales vacíos si no son necesarios.
- Presupuesto: **Estante a medida — ejemplo**. Alcance: “Fabricación e instalación de un estante de ejemplo”; exclusión: “No incluye pintura”.
- Ítem: “Estante — ejemplo”, cantidad `1`, precio unitario `1000.00`, descuento e impuesto `0`, moneda ARS. Vencimiento futuro y condiciones ficticias breves.
- Total esperado: **ARS 1.000,00**. Cobro manual ficticio: **ARS 250,00**; saldo esperado: **ARS 750,00**.
- Próxima acción: “Confirmar medidas — ejemplo”; elegir fechas coherentes con el día de la captura. Para mostrar vencidos/entregas, usar los ejemplos identificados de la demo o preparar datos deliberadamente ficticios.

No usar estos importes como precio comercial de TrabajoClaro. Para mostrar USD, crear otro documento en USD; no sumar ambas monedas.

## Pantallas existentes y material sugerido

| Pantalla o función | Qué mostrar | Qué explica | Formato sugerido | Datos ficticios necesarios |
| --- | --- | --- | --- | --- |
| Landing | Propuesta, aviso de beta, Probar demo y Crear cuenta de prueba | Qué intenta resolver y cómo se empieza; no prueba adopción | Escritorio y móvil | Ningún dato propio; los ejemplos visibles ya están identificados |
| Panel “Qué requiere atención” | Pendientes, próximas acciones, entregas y saldos de ejemplo | Priorización de información cargada; no son métricas de mercado | Móvil y escritorio | Demo con estados/fechas coherentes y claramente ficticios |
| Cliente | Nombre de ejemplo y documentos asociados | Punto de partida y trazabilidad del recorrido | Móvil o captura de escritorio | Cliente de ejemplo sin información personal |
| Creación de presupuesto | Cliente, alcance, exclusiones, ítem, moneda y condiciones | Cómo se estructura una propuesta antes de publicarla | Grabación y móvil | Presupuesto del conjunto sugerido, sin enlaces privados |
| Revisión del borrador | Total calculado, descuento/impuesto en cero y botón de publicar | Guardar y publicar son pasos distintos | Escritorio | Total ARS 1.000,00; versión todavía no compartida |
| Publicación/versiones | Que existe una versión y se puede revocar | Conservación de acuerdos; no equivale a envío | Grabación recortada de la zona de versiones | Una versión ficticia; **ocultar campo de enlace y barra de URL** |
| Vista pública del cliente | Alcance, ítems y total sin sesión propietaria | El cliente puede revisar sin crearse cuenta | Móvil y escritorio, área de contenido | Versión vigente ficticia; URL y token fuera del encuadre |
| Aceptación | Nombre ficticio, decisión y confirmación; luego estado aceptado | Una decisión se asocia a una versión; no es firma digital certificada | Grabación breve de contenido | Nombre “Cliente de ejemplo”, sin contacto real |
| Conversión a trabajo | Crear/abrir trabajo y alcance/importe conservados | Reutiliza lo aceptado; la operación no duplica trabajos | Grabación de escritorio | Presupuesto ya aceptado, ARS 1.000,00 |
| Tablero de trabajos | Columnas de estados, tarjetas y próxima acción | Organización visual del trabajo; **no mostrar arrastre**, porque no está implementado | Escritorio; móvil solo con el área visible y desplazamiento del tablero | Trabajos de ejemplo en estados distintos |
| Estado y próxima acción | Formulario con estado, entrega y acción | Cómo se actualiza un trabajo realmente | Móvil o grabación | Acción y fecha ficticias, coherentes con el ejemplo |
| Registro de cobro | Importe, fecha/medio, confirmación y saldo posterior | Registro interno de un pago recibido fuera de la app; no procesamiento de dinero | Grabación y móvil | Importe 250.00 ARS y referencia vacía o “Ejemplo” |
| Anulación e historial | Motivo y saldo que vuelve a cambiar | Corrección con trazabilidad; no borrado silencioso | Grabación de escritorio | Motivo “Carga ficticia duplicada” y movimiento de ejemplo |
| Exportación | Botón CSV y archivo solo con filas ficticias | Portabilidad del propio negocio | Grabación de descarga o captura del botón | Evitar abrir descargas personales o rutas del equipo |
| Feedback | Pantalla seleccionada, comentario, carga y confirmación | El comentario se almacena con versión; contacto opcional | Móvil y escritorio | Texto “Ejemplo de feedback”; contacto vacío |
| Configuración de prueba | Identificador de beta y moneda/configuración permitida | Contexto de la prueba y separación por moneda | Captura recortada | Solo datos ficticios de negocio, no secretos ni archivos `.env` |

Las rutas de navegación están en [web.php](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/routes/web.php). El documento del cliente tiene un enlace privado generado por la aplicación: no se ofrece un token de ejemplo reutilizable en esta guía.

## Grabación del recorrido completo

Orden recomendado de acciones, sin texto promocional prefabricado:

1. Mostrar el aviso de beta y entrar a la demo o a una cuenta propia de prueba.
2. Crear cliente/presupuesto ficticios y revisar el total antes de publicar.
3. Publicar fuera del encuadre del enlace; abrirlo en otro perfil o ventana privada sin grabar la barra de direcciones.
4. Revisar y aceptar con el nombre ficticio y la casilla de confirmación.
5. Volver al propietario, convertir y actualizar el trabajo desde su formulario.
6. Registrar el cobro ficticio, mostrar saldo y distinguir trabajo terminado de cobro completo.
7. Mostrar que se puede exportar o enviar feedback.

Si un paso falla, resolverlo o registrar la limitación. No montar pantallas de éxito para aparentar que el backend lo ejecutó. No acelerar una carga de modo que parezca una medición de rendimiento acreditada.

## Privacidad y exactitud del encuadre

- Capturar el contenido de la app: ocultar barra de direcciones, pestañas, marcadores, herramientas de desarrollo, cookies, terminales y notificaciones del equipo.
- No mostrar el enlace completo del presupuesto ni su campo de copia, aunque los datos sean ficticios. Revocar enlaces usados para una demostración cuando ya no se necesiten.
- No fotografiar `.env`, contraseñas, tokens, headers de autorización, dumps, SQL con datos, paths de secretos ni paneles administrativos del proveedor.
- Usar teléfono/contacto vacío. Para demostrar WhatsApp, hace falta un número internacional de prueba controlado y autorizado; no inventar un número que pueda pertenecer a alguien. No hace falta enviarlo y no se debe afirmar que se envió.
- No usar datos de una cuenta de otro tester; la demo propia no permite verlos. No mostrar capturas del sistema operativo que expongan información real.
- Mantener visibles la identificación de beta y de ejemplo cuando ayuden a evitar confusión. Los números del panel son datos ficticios, no usuarios, ventas o resultados del producto.
- Revisar que no haya desborde, texto recortado, foco accidental sobre un dato sensible o controles superpuestos. Evitar encuadres donde la versión o los avisos parezcan prometer más que lo implementado.
- No presentar PDF, confirmación o registro de cobro como comprobante fiscal, pago procesado o firma certificada.

## Herramientas disponibles y límites

Las pruebas [journey.spec.js](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/tests/Browser/journey.spec.js) y [beta.spec.js](https://github.com/Xenov2030/TrabajoClaro/blob/3309445/tests/Browser/beta.spec.js) generan capturas y PDF con Chromium para 360×800 y 1440×1000. Ejecutarlas siguiendo README; crean cuentas/demos/feedback ficticios y pueden consumir los límites por IP. Los resultados se guardan localmente en `test-results/`, ignorado por Git.

La suite captura imágenes, no produce automáticamente una grabación publicable ni revisa todas las escenas de esta tabla. Las capturas previas no acreditan una URL pública, dispositivos físicos, Safari/Firefox ni la aprobación editorial del autor. Revisar manualmente cualquier material antes de difundirlo.
