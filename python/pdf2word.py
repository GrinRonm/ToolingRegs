#!/usr/bin/env python3
import sys
import json
from pdf2docx import Converter
import os

def main():
    if len(sys.argv) < 3:
        print(json.dumps({'error': 'Missing arguments'}))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        cv = Converter(input_path)
        cv.convert(output_path, start=0, end=None)
        cv.close()

        print(json.dumps({'success': True, 'path': output_path}))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
