#!/usr/bin/env python3
"""
OCR Handler — Extract text from image using Tesseract
Usage: python3 ocr_handler.py <image_path> <lang>
Output: JSON to stdout
"""

import sys
import json

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No image path provided"}))
        sys.exit(1)

    image_path = sys.argv[1]
    lang = sys.argv[2] if len(sys.argv) > 2 else "eng+rus"

    # User can now explicitly choose between eng+rus and rus+eng
    
    try:
        import pytesseract
        from PIL import Image, ImageOps, ImageEnhance, ImageStat

        # Open and preprocess image
        img = Image.open(image_path)

        if img.mode in ('RGBA', 'P'):
            # Paste on white background to avoid transparent blackness
            bg = Image.new("RGB", img.size, (255, 255, 255))
            if img.mode == 'RGBA':
                bg.paste(img, mask=img.split()[3])
            else:
                bg.paste(img)
            img = bg
        elif img.mode != 'RGB':
            img = img.convert('RGB')

        # 1. Convert to grayscale
        img = img.convert('L')

        # 2. Invert if the background is dark (Dark Mode Screenshots)
        stat = ImageStat.Stat(img)
        mean_brightness = stat.mean[0]
        if mean_brightness < 128:
            img = ImageOps.invert(img)

        # 3. Upscale the image for better OCR accuracy
        width, height = img.size
        if width < 2000 or height < 2000:
            scale_factor = 2
            img = img.resize((width * scale_factor, height * scale_factor), Image.LANCZOS)

        # 4. Enhance contrast heavily
        enhancer = ImageEnhance.Contrast(img)
        img = enhancer.enhance(2.5)

        # 5. Add white padding (Tesseract needs margins)
        img = ImageOps.expand(img, border=50, fill='white')

        # Run OCR with config parameters
        # --oem 1: Neural nets LSTM only
        # --psm 4: Assume a single column of text of variable sizes
        # (This is usually best for chat screenshots and mobile screenshots)
        custom_config = r'--oem 1 --psm 4'
        text = pytesseract.image_to_string(img, lang=lang, config=custom_config)
        
        # Clean up the text
        # Remove extra blank lines that Tesseract sometimes adds
        lines = [line.strip() for line in text.split('\n')]
        # Filter out lines that are just random single punctuation marks
        cleaned_lines = []
        for line in lines:
            if not line:
                cleaned_lines.append("")
                continue
            # if line is just noise like "|", "_", "~"
            if len(line) <= 2 and not any(c.isalnum() for c in line):
                continue
            cleaned_lines.append(line)
            
        text = '\n'.join(cleaned_lines).strip()
        # Collapse multiple blank lines into one
        import re
        text = re.sub(r'\n{3,}', '\n\n', text)

        if not text.strip():
            # Fallback to default PSM 3
            custom_config = r'--oem 1 --psm 3'
            text = pytesseract.image_to_string(img, lang=lang, config=custom_config).strip()

        if not text.strip():
            text = "(Текст не найден)"

        print(json.dumps({
            "success": True,
            "text": text
        }, ensure_ascii=False))

    except ImportError as e:
        print(json.dumps({
            "success": False,
            "error": f"Missing dependency: {str(e)}"
        }))
        sys.exit(1)

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e)
        }))
        sys.exit(1)


if __name__ == "__main__":
    main()
