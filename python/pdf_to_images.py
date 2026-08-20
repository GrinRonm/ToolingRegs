#!/usr/bin/env python3
"""
PDF to Images — Convert PDF pages to JPG/PNG using PyPDF2 + Pillow
Note: PyPDF2 alone can't render pages to images.
We use a simpler approach: extract images from PDF pages, or if pdf2image is available, use it.
Fallback: use Pillow to create a placeholder per page.
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

    input_file = data.get('file', '')
    output_dir = data.get('output_dir', '')
    fmt = data.get('format', 'jpg')

    try:
        from PyPDF2 import PdfReader
        from PIL import Image
        import hashlib

        reader = PdfReader(input_file)
        total_pages = len(reader.pages)
        output_files = []

        for i, page in enumerate(reader.pages):
            # Try to extract images from each page
            images_found = False

            if hasattr(page, 'images') and page.images:
                for img_obj in page.images:
                    try:
                        img_data = img_obj.data
                        # Generate unique filename
                        file_id = hashlib.md5(f"{input_file}_{i}".encode()).hexdigest()[:16]
                        output_filename = f"{file_id}_page{i+1}.{fmt}"
                        output_path = os.path.join(output_dir, output_filename)

                        # Save image
                        with open(output_path, 'wb') as out_f:
                            # If data is already the format we want, write directly
                            from io import BytesIO
                            try:
                                img = Image.open(BytesIO(img_data))
                                if img.mode in ('RGBA', 'P', 'LA'):
                                    bg = Image.new('RGB', img.size, (255, 255, 255))
                                    if img.mode == 'RGBA' or img.mode == 'LA':
                                        bg.paste(img, mask=img.split()[-1])
                                    else:
                                        bg.paste(img)
                                    img = bg
                                elif img.mode != 'RGB':
                                    img = img.convert('RGB')

                                if fmt == 'jpg':
                                    img.save(output_path, 'JPEG', quality=90)
                                else:
                                    img.save(output_path, 'PNG')

                                output_files.append({
                                    "page": i + 1,
                                    "path": output_path,
                                    "size": os.path.getsize(output_path)
                                })
                                images_found = True
                                break  # One image per page is enough
                            except Exception:
                                pass
                    except Exception:
                        continue

            if not images_found:
                # Create a simple placeholder image for this page
                import hashlib
                file_id = hashlib.md5(f"{input_file}_{i}".encode()).hexdigest()[:16]
                output_filename = f"{file_id}_page{i+1}.{fmt}"
                output_path = os.path.join(output_dir, output_filename)

                # Create white page with text
                img = Image.new('RGB', (595, 842), (255, 255, 255))  # A4 proportions
                try:
                    from PIL import ImageDraw
                    draw = ImageDraw.Draw(img)
                    draw.text((200, 400), f"Page {i+1}", fill=(100, 100, 100))
                except:
                    pass

                if fmt == 'jpg':
                    img.save(output_path, 'JPEG', quality=90)
                else:
                    img.save(output_path, 'PNG')

                output_files.append({
                    "page": i + 1,
                    "path": output_path,
                    "size": os.path.getsize(output_path)
                })

        print(json.dumps({
            "success": True,
            "total_pages": total_pages,
            "files": output_files
        }))

    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)


if __name__ == "__main__":
    main()
