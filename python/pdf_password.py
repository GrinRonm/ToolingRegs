#!/usr/bin/env python3
import sys
import json
from PyPDF2 import PdfReader, PdfWriter
import os

def main():
    if len(sys.argv) < 4:
        print(json.dumps({'error': 'Missing arguments'}))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]
    password = sys.argv[3]

    try:
        reader = PdfReader(input_path)
        writer = PdfWriter()

        for page in reader.pages:
            writer.add_page(page)

        writer.encrypt(password)

        with open(output_path, "wb") as f:
            writer.write(f)

        print(json.dumps({'success': True, 'path': output_path}))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
