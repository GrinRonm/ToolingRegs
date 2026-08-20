#!/usr/bin/env python3
import sys
import json
from rembg import remove
from PIL import Image
import os

def main():
    if len(sys.argv) < 3:
        print(json.dumps({'error': 'Missing arguments'}))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        if not os.path.exists(input_path):
            raise Exception("Input file not found")
            
        input_image = Image.open(input_path)
        output_image = remove(input_image)
        output_image.save(output_path, "PNG")

        print(json.dumps({'success': True, 'path': output_path}))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
