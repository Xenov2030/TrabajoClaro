#!/usr/bin/env python3
"""Codificar ICO multirresolución desde el PNG original de 512 generado por Chromium."""
from pathlib import Path
from PIL import Image
root = Path(__file__).resolve().parents[1] / 'public'
with Image.open(root / 'icon-512.png') as source:
    assert source.format == 'PNG' and source.size == (512, 512)
    source.save(root / 'favicon.ico', format='ICO', sizes=[(16, 16), (32, 32), (48, 48)], bitmap_format='bmp')
print('favicon.ico: 16, 32 y 48 píxeles.')
