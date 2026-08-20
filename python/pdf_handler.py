#!/usr/bin/env python3
"""
PDF Handler — Merge, Split, Compress PDFs using PyPDF2
Usage: python3 pdf_handler.py <input_json_file>
"""

import sys
import json
import os

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No input file"}))
        sys.exit(1)

    with open(sys.argv[1], 'r') as f:
        data = json.load(f)

    action = data.get('action', '')

    try:
        if action == 'merge':
            merge_pdfs(data)
        elif action == 'split':
            split_pdf(data)
        elif action == 'compress':
            compress_pdf(data)
        else:
            print(json.dumps({"success": False, "error": f"Unknown action: {action}"}))
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)


def merge_pdfs(data):
    from PyPDF2 import PdfMerger

    files = data.get('files', [])
    output = data.get('output', '')

    if len(files) < 2:
        print(json.dumps({"success": False, "error": "Need at least 2 files"}))
        return

    merger = PdfMerger()
    total_pages = 0

    for f in files:
        merger.append(f)

    merger.write(output)
    merger.close()

    # Count pages
    from PyPDF2 import PdfReader
    reader = PdfReader(output)
    total_pages = len(reader.pages)

    print(json.dumps({
        "success": True,
        "pages": total_pages,
        "size": os.path.getsize(output)
    }))


def split_pdf(data):
    from PyPDF2 import PdfReader, PdfWriter

    input_file = data.get('file', '')
    output_dir = data.get('output_dir', '')

    reader = PdfReader(input_file)
    total_pages = len(reader.pages)
    output_files = []

    for i in range(total_pages):
        writer = PdfWriter()
        writer.add_page(reader.pages[i])

        output_path = os.path.join(output_dir, f"page_{i+1}.pdf")
        with open(output_path, 'wb') as f:
            writer.write(f)

        output_files.append({
            "page": i + 1,
            "path": output_path,
            "filename": f"page_{i+1}.pdf",
            "size": os.path.getsize(output_path)
        })

    print(json.dumps({
        "success": True,
        "total_pages": total_pages,
        "files": output_files
    }))


def compress_pdf(data):
    """Compress PDF by removing unnecessary data and compressing streams"""
    from PyPDF2 import PdfReader, PdfWriter

    input_file = data.get('file', '')
    output = data.get('output', '')

    reader = PdfReader(input_file)
    writer = PdfWriter()

    for page in reader.pages:
        page.compress_content_streams()
        writer.add_page(page)

    # Remove metadata for smaller size
    writer.add_metadata({})

    with open(output, 'wb') as f:
        writer.write(f)

    original_size = os.path.getsize(input_file)
    compressed_size = os.path.getsize(output)

    print(json.dumps({
        "success": True,
        "original_size": original_size,
        "compressed_size": compressed_size,
        "pages": len(reader.pages)
    }))


if __name__ == "__main__":
    main()
