#!/usr/bin/env python3
"""
QR Handler — Generate QR code using qrcode and PIL
Usage: python3 qr_handler.py <input_json_file>
"""

import sys
import json
import os

def hex_to_rgb(hex_color):
    hex_color = hex_color.lstrip('#')
    if len(hex_color) == 3:
        hex_color = ''.join(c + c for c in hex_color)
    if len(hex_color) != 6:
        return (0, 0, 0)
    return tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No input file"}))
        sys.exit(1)

    try:
        with open(sys.argv[1], 'r') as f:
            data = json.load(f)

        text = data.get('text', '')
        size = data.get('size', 300)
        color = data.get('color', '#000000')
        output = data.get('output', '')

        if not text:
            print(json.dumps({"success": False, "error": "Empty text"}))
            sys.exit(1)

        import qrcode
        from PIL import Image

        qr = qrcode.QRCode(
            version=None,
            error_correction=qrcode.constants.ERROR_CORRECT_H,
            box_size=10,
            border=4,
        )
        qr.add_data(text)
        qr.make(fit=True)

        fill_color = hex_to_rgb(color)
        img = qr.make_image(fill_color=fill_color, back_color="white")
        
        # Resize to requested size
        img = img.resize((size, size), Image.NEAREST)
        
        img.save(output)

        print(json.dumps({"success": True}))

    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)


if __name__ == "__main__":
    main()
