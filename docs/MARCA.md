> Actualización de arquitectura: la marca se conserva en Next.js; los componentes actuales están en `src/components/brand.js`. Las referencias a verificaciones Laravel describen la etapa anterior; ver [verificación de migración](VERIFICACION_MIGRACION.md).

# TrabajoClaro · identidad de marca provisional

**Nombre provisional:** TrabajoClaro.
**Eslogan:** Cada trabajo en orden. Cada cobro, claro.

Identidad implementada para pequeños negocios de servicios y fabricación por encargo. Busca comunicar claridad, confianza, cercanía y control; no representa resultados comerciales acreditados. El nombre sigue siendo provisional: la comprobación externa está pendiente.

## Concepto y construcción

El símbolo combina un **marco abierto** con un **recorrido que sube un nivel**. El marco organiza; el carril interior y su cambio de nivel sugieren avance, sin una flecha o un check genéricos. Se dibujó específicamente para el proyecto sobre una grilla de 64 unidades, con dos trazados simples y un contenedor de esquinas redondeadas. El espacio negativo y los espesores se mantienen al reducirlo.

No se partió de un banco de iconos, clipart, emojis ni un logo competidor. No se usó el logo de Nicolas Aguirres. La autoría geométrica de este recurso no implica exclusividad legal ni garantiza que no existan símbolos similares.

El logotipo horizontal une el símbolo con **TrabajoClaro**, con lettering Manrope 700 convertido a curvas. Las versiones SVG para presentaciones incluyen el eslogan también en curvas, para que se abran sin fuentes instaladas. **En la aplicación el eslogan es texto HTML**, seleccionable y adaptable; el nombre/símbolo llevan texto alternativo accesible.

## Paleta y tokens

| Token CSS | Color | Uso |
| --- | --- | --- |
| `--brand-primary` | `#16634f` | Acción, símbolo sobre fondos claros y favicon |
| `--brand-deep` | `#103c33` | Fondo oscuro de presentaciones |
| `--brand-soft` | `#dbeac4` | Contraparte luminosa del símbolo y detalles sobre oscuro |
| `--brand-ink` | `#172f30` | Nombre y texto principal sobre claro; variante monocromática |
| `--brand-paper` | `#f6f7f2` | Superficie clara cálida |
| `--font-brand`, `--font-ui` | Manrope y respaldo de sistema | Tipografía compartida de marca/interfaz |

Los tokens de UI existentes `--green`, `--ink` y `--paper` referencian los de marca, conservando la coherencia de botones/formularios. Se definen en [app.css](../src/app/globals.css); los SVG usan los mismos valores en su generador.

Contraste calculado mediante luminancia sRGB:

- Texto principal sobre fondo: **13,13:1**.
- Texto secundario `#536765` sobre fondo: **5,58:1**.
- Blanco sobre verde principal: **7,16:1**.
- Texto claro `#f5f8f0` sobre verde oscuro: **11,40:1**.
- Acento luminoso sobre verde oscuro: **9,66:1**.

No usar el acento luminoso como texto sobre blanco. Estos valores verifican combinaciones de paleta, no certifican toda la accesibilidad de la aplicación.

## Tipografía y licencia

**Manrope variable**, eje `wght` 200–800, diseñada por Mikhail Sharanda según sus metadatos. Procedencia: Google Fonts, proyecto Manrope.

- Archivo local: [Manrope-Variable.ttf](../public/fonts/manrope/Manrope-Variable.ttf).
- Licencia original íntegra: [OFL.txt](../public/fonts/manrope/OFL.txt), **SIL Open Font License 1.1**.
- Copyright declarado en el archivo: *2018 The Manrope Project Authors*.
- Procedencia verificable: [SOURCE.json](../public/fonts/manrope/SOURCE.json).
- Repositorio: https://github.com/google/fonts.
- Commit consultado: `6c1dbe27dcf7d817d4b62cec033ba77fa8e2a5ec`.
- Ruta original: `ofl/manrope/Manrope[wght].ttf`.
- SHA-256 del TTF sin modificar: `3ae11c49db0455a3cc33e37d380f20fdb8c7f8b41dc07625c177e3d87a9d6ae6`.

Vite empaqueta una copia con nombre hash para servirla desde la propia aplicación; no se consulta un CDN de fuentes al visitar las pantallas. `font-display: swap` permite usar el respaldo mientras carga. Los SVG de logotipo usan curvas y no necesitan descargar la fuente para verse. Conservar OFL y procedencia al redistribuir la fuente; no presentar su diseño tipográfico como una creación del autor del producto.

## Variantes y archivos entregados

| Uso | Archivos en `public/brand/` |
| --- | --- |
| Horizontal sobre claro | [logo-horizontal.svg](../public/brand/logo-horizontal.svg) |
| Horizontal sobre oscuro | [logo-horizontal-dark.svg](../public/brand/logo-horizontal-dark.svg) |
| Horizontal monocromático | [logo-horizontal-mono.svg](../public/brand/logo-horizontal-mono.svg) |
| Presentación con eslogan, claro | [logo-tagline.svg](../public/brand/logo-tagline.svg) |
| Presentación con eslogan, oscuro | [logo-tagline-dark.svg](../public/brand/logo-tagline-dark.svg) |
| Presentación con eslogan, una tinta | [logo-tagline-mono.svg](../public/brand/logo-tagline-mono.svg) |
| Símbolo independiente, claro/oscuro/mono | [symbol.svg](../public/brand/symbol.svg), [symbol-dark.svg](../public/brand/symbol-dark.svg), [symbol-mono.svg](../public/brand/symbol-mono.svg) |
| Grilla y geometría reproducible | [geometry.json](../public/brand/geometry.json) |

Los nueve SVG son vectores reales, sin mapas de bits incrustados ni referencias externas para el lettering. La variante monocromática utiliza una tinta oscura y espacio negativo. El símbolo puede usarse como avatar de producto; no sustituye automáticamente las iniciales de clientes o negocios.

### Favicon e iconos

| Archivo | Formato real y dimensiones |
| --- | --- |
| [favicon.svg](../public/favicon.svg) | SVG de símbolo, viewBox 64×64, sin nombre ni eslogan visibles |
| [favicon.ico](../public/favicon.ico) | ICO multirresolución con imágenes 16×16, 32×32 y 48×48 |
| [favicon-16x16.png](../public/favicon-16x16.png) | PNG de 16×16 para inspección/uso compacto |
| [favicon-32x32.png](../public/favicon-32x32.png) | PNG de 32×32 |
| [apple-touch-icon.png](../public/apple-touch-icon.png) | PNG de 180×180 |
| [icon-192.png](../public/icon-192.png) | PNG de 192×192 |
| [icon-512.png](../public/icon-512.png) | PNG de 512×512 |
| [site.webmanifest](../public/site.webmanifest) | JSON con id estable, iconos any/maskable y `display: standalone` |

La implementación actual incorpora instalación PWA y manifest standalone, con iconos maskable 192/512 y worker sin caché privada ni modo sin conexión. Su instalación real sigue pendiente de comprobación física; ver [PWA](PWA.md). Los iconos son rasterizaciones/codificaciones reales; no se renombró un archivo para simular otro formato.

## Integración y uso

- Componente reutilizable: [Brand](../src/components/brand.js).
- Metadatos e iconos: [layout Next.js](../src/app/layout.js).
- Encabezado de landing, acceso/registro, panel, demo y pantallas internas: logo horizontal sin eslogan.
- Landing: eslogan HTML como título principal. Acceso/registro: lockup con eslogan HTML. Documentos públicos: marca en la cabecera de la web y versión compacta monocromática al imprimir.
- Móvil: tamaño compacto, sin eslogan dentro del encabezado; no se estira el recurso. Presentaciones amplias: variante con eslogan.
- Muestra local: ruta **`/marca`** de la aplicación. Incluye variantes sobre claro/oscuro, descarga SVG y vista nativa a 16/32/48/64 px. Es una ruta local del proyecto, no una URL pública desplegada.
- Firma del desarrollador: [Author](../src/components/brand.js), separada del símbolo/nombre del producto. Conserva el texto y enlace al portfolio existentes, también en el PDF.

Mantener proporciones originales, margen libre alrededor del símbolo (al menos 1/4 de su lado en usos aislados) y contraste. No añadir detalles al favicon ni comprimir el eslogan para que entre a la fuerza. Para impresión, utilizar la variante monocromática sobre blanco.

## Regeneración de los recursos

Los recursos listos para usar están en `public/`; no se necesita Python para ejecutar Laravel. Para regenerarlos en desarrollo hacen falta **Python 3, fontTools y Pillow**, más las dependencias npm del proyecto y Chromium disponible. El TTF/licencia ya están incluidos, no hace falta descargarlos de nuevo.

Desde la raíz:

```sh
python3 scripts/generate-brand.py
node scripts/render-brand-icons.mjs
python3 scripts/encode-brand-ico.py
npm run build
```

El primer script genera SVG desde la grilla y la tipografía; el segundo rasteriza los PNG mediante Chromium; el tercero codifica el ICO. El binario Chromium se puede seleccionar con `PLAYWRIGHT_CHROMIUM_EXECUTABLE`. La instalación de herramientas opcionales se gestiona fuera del runtime PHP; no se agregaron servicios externos ni procesamiento de imágenes al backend.

## Comprobación del nombre y dominios

**Resultado: no determinada. Nombre y disponibilidad legal pendientes.** No hay herramienta de búsqueda web conectada; la red administrada permite destinos de paquetes, no los sitios de búsqueda/registro consultados.

| Intento de consulta | Destino | Resultado observado |
| --- | --- | --- |
| Coincidencias de producto: nombre exacto y presupuestos/trabajos/cobros | Búsqueda Bing, `www.bing.com` | Túnel del proxy rechazado con 403; sin respuesta del buscador ni resultados inspeccionables |
| Estado de `trabajoclaro.com` | RDAP, `rdap.verisign.com/com/v1/domain/trabajoclaro.com` | Túnel rechazado con 403; no se obtuvo registro ni disponibilidad |
| Estado de `trabajoclaro.com.ar` | Intento RDAP, `rdap.nic.ar/domain/trabajoclaro.com.ar` | Túnel rechazado con 403; tampoco se pudo validar el endpoint o su respuesta |

Las consultas se intentaron a través del proxy existente, sin cambiar la política, desactivar TLS ni registrar nada. Un rechazo del proxy **no indica que el dominio esté registrado, libre o que no haya un producto con ese nombre**.

No se revisaron registros de marcas (INPI u otras jurisdicciones), clases, nombres semejantes, titulares, usos no indexados ni similitud visual con todos los símbolos existentes. No se realizó una búsqueda legal ni se afirma exclusividad. No se registraron marcas o dominios.

Se conserva **TrabajoClaro como nombre provisional**. Antes de consolidarlo o invertir en difusión, completar búsqueda de productos similares, dominios y marcas con acceso habilitado y asesoramiento adecuado. Si surge un conflicto relevante, documentarlo y presentar tres alternativas antes de decidir una sustitución; no se cambió el nombre sin evidencia.

## Control visual y técnico

- Revisados logotipos sobre claro/oscuro, mono, eslogan HTML, encabezados y favicon a 16/32 px en la muestra local.
- Chromium a **360×800 y 1440×1000**: las ocho pruebas de navegador cubren además registro/login, demo, presupuesto público/PDF, feedback y firma. Se comprobó carga de fuente/imágenes, metadatos, descargas y ausencia de desborde horizontal.
- Comprobados con parsers: SVG válidos y trazados dentro del viewBox; PNG con firma binaria y dimensiones reales; ICO con sus tres tamaños. La fuente se abrió con fontTools y su hash coincide con la procedencia registrada.
- Contrastes de paleta calculados; foco visible y firma del autor conservados.
- No se verificaron la barra de pestañas en todos los navegadores, una pantalla de inicio iOS/Android física, todos los lectores PDF, una certificación de accesibilidad ni una URL pública. No se puede concluir disponibilidad legal del nombre a partir de estas comprobaciones.

Iconos de instalación actualizados: [maskable 192](../public/icon-192-maskable.png) y [maskable 512](../public/icon-512-maskable.png), con fondo opaco verde y símbolo esencial dentro de la zona segura central.
