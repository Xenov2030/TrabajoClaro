#!/usr/bin/env python3
"""Generar SVG originales y lettering en curvas desde Manrope con licencia OFL."""
from pathlib import Path
from fontTools.ttLib import TTFont
from fontTools.varLib.instancer import instantiateVariableFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
import json

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'public/brand'
OUT.mkdir(parents=True, exist_ok=True)
FONT = ROOT / 'public/fonts/manrope/Manrope-Variable.ttf'
GREEN, INK, DEEP, SOFT = '#16634f', '#172f30', '#103c33', '#dbeac4'
SLOGAN = 'Cada trabajo en orden. Cada cobro, claro.'
# Marco abierto y recorrido que sube un nivel. Geometría propia sobre grilla 64.
FRAME = 'M44 12H24c-6.627 0-12 5.373-12 12v16c0 6.627 5.373 12 12 12h18v-8H24a2 2 0 0 1-2-2V24a2 2 0 0 1 2-2h20V12Z'
STEP = 'M30 30h14V20h10v20H30V30Z'

def symbol(bg=GREEN, fg='#ffffff', accent=SOFT):
    return f'<rect width="64" height="64" rx="14" fill="{bg}"/><path d="{FRAME}" fill="{fg}"/><path d="{STEP}" fill="{accent}"/>'

def lettering(text, size, weight, x, baseline, fill, spacing=0):
    font = instantiateVariableFont(TTFont(FONT), {'wght': weight}, inplace=False)
    glyphs, cmap = font.getGlyphSet(), font.getBestCmap()
    scale, cursor, paths = size/font['head'].unitsPerEm, x, []
    for char in text:
        name = cmap[ord(char)]
        pen = SVGPathPen(glyphs, ntos=lambda n: str(round(n, 3)))
        glyphs[name].draw(TransformPen(pen, (scale, 0, 0, -scale, cursor, baseline)))
        data = pen.getCommands()
        if data: paths.append(f'<path d="{data}"/>')
        cursor += glyphs[name].width*scale + spacing
    return f'<g fill="{fill}">'+''.join(paths)+'</g>', cursor-x-spacing

def svg(body, width, height, title, desc=''):
    return f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}" viewBox="0 0 {width} {height}" role="img" aria-labelledby="title desc"><title id="title">{title}</title><desc id="desc">{desc}</desc>{body}</svg>\n'

variants = {'light':(GREEN,'#fff',SOFT,INK), 'dark':(SOFT,DEEP,DEEP,'#f5f8f0'), 'mono':(INK,'#fff','#fff',INK)}
width = None
for variant,(bg,fg,accent,text) in variants.items():
    suffix = '' if variant=='light' else '-'+variant
    word, advance = lettering('TrabajoClaro', 38, 700, 82, 45, text, -.65)
    width = round(82+advance+2, 2)
    base = symbol(bg,fg,accent)
    (OUT/f'symbol{suffix}.svg').write_text(svg(base,64,64,'Símbolo de TrabajoClaro','Marco abierto con un recorrido ascendente.'))
    (OUT/f'logo-horizontal{suffix}.svg').write_text(svg(base+word,width,64,'TrabajoClaro','Logotipo horizontal. Símbolo y nombre.'))
    tagline, tagline_width = lettering(SLOGAN, 14, 500, 82, 80, text)
    (OUT/f'logo-tagline{suffix}.svg').write_text(svg(base+word+tagline,round(max(width,82+tagline_width+2),2),90,'TrabajoClaro — '+SLOGAN,'Versión para presentaciones; el eslogan usa HTML en la aplicación.'))
(ROOT/'public/favicon.svg').write_text(svg(symbol(),64,64,'Símbolo','Marco abierto con un recorrido ascendente.'))
(OUT/'geometry.json').write_text(json.dumps({'canvas':64,'frame_path':FRAME,'step_path':STEP,'horizontal_width':width,'horizontal_height':64,'tagline':SLOGAN},indent=2)+'\n')
print('SVG generados: 9 variantes de marca y favicon.svg')
