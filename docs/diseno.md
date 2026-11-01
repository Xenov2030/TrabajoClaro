# Sistema visual de TrabajoClaro · beta 2

Identidad propia: superficie clara cálida, texto oscuro y verde profundo. Manrope variable alojada localmente con licencia OFL y respaldo de sistema; jerarquía, aire y acciones claras antes que gráficos decorativos.

| Token | Valor y uso |
| --- | --- |
| Fondo | `#f6f7f2` |
| Superficie | `#ffffff` |
| Texto | `#172f30` |
| Texto secundario | `#536765` |
| Acción | `#16634f`, con texto blanco |
| Borde de controles | `#7b8f84` |
| Espaciado | 4, 8, 12, 16, 24 y 32 px; bloques mayores de 40–76 px |
| Radio | Controles 6–7 px; tarjetas 9–10 px |
| Foco | Contorno visible de 3 px; navegación y skip link por teclado |

Contrastes calculados con luminancia sRGB: texto/fondo 13,13:1; secundario/fondo 5,58:1; botón blanco/verde 7,16:1; borde/blanco 3,44:1. Son verificaciones de la paleta, no una certificación completa de accesibilidad.

Botones primarios verdes, secundarios delineados y deshabilitados explícitos. Etiquetas siempre visibles; indicación de obligatorio y errores junto al campo/resumen. Carga con `aria-busy`, texto, spinner CSS y protección de doble clic; respeta reducción de movimiento y se restablece al volver por el historial. Confirmación mediante mensajes con rol status. Estados vacíos explican el siguiente paso. Los estados no dependen solo del color.

Los íconos son SVG propios o símbolos tipográficos simples; no se usan emojis como íconos. No hay logos, testimonios ni cifras de clientes inventados. Los ejemplos de la demo están identificados como ficticios.

En celular se apilan formularios y tarjetas; el presupuesto público usa tarjetas por ítem, conservando tabla para escritorio/impresión. La navegación muestra las cinco opciones completas. Campos a 16 px para evitar zoom inesperado en móviles, controles cómodos y encabezados/textos largos con ajuste de línea.

Revisión realizada en Chromium a 360×800 y 1440×1000: landing, panel, editor, presupuesto público, feedback cargando/confirmado y registro. Los recorridos comprueban ancho del documento y errores JavaScript. No se verificaron Safari, Firefox, zoom asistivo exhaustivo ni dispositivos físicos; quedan para el piloto conectado a un dominio real.

La identidad gráfica, variantes SVG, favicon, tipografía y límites de la revisión del nombre están documentados en [MARCA.md](MARCA.md). La muestra local se abre en `/marca`.
