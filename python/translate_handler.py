#!/usr/bin/env python3
"""
Translate Handler — Translate text using deep-translator (Google Translate)
Usage: python3 translate_handler.py <input_json_file>
Input JSON: {"text": "...", "source": "en", "target": "ru"}
Output: JSON to stdout
"""

import sys
import json


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No input file provided"}))
        sys.exit(1)

    input_file = sys.argv[1]

    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)

        text = data.get('text', '')
        source = data.get('source', 'en')
        target = data.get('target', 'ru')

        if not text.strip():
            print(json.dumps({"success": False, "error": "Empty text"}))
            sys.exit(1)

        from deep_translator import GoogleTranslator

        # deep-translator has a limit of ~5000 chars per request
        # Split long texts into chunks
        max_chunk = 4500
        chunks = []
        current = ""

        for line in text.split('\n'):
            if len(current) + len(line) + 1 > max_chunk:
                if current:
                    chunks.append(current)
                current = line
            else:
                current = current + '\n' + line if current else line

        if current:
            chunks.append(current)

        # Translate each chunk
        translated_chunks = []
        translator = GoogleTranslator(source=source, target=target)

        for chunk in chunks:
            result = translator.translate(chunk)
            translated_chunks.append(result)

        translated_text = '\n'.join(translated_chunks)

        print(json.dumps({
            "success": True,
            "text": translated_text
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
