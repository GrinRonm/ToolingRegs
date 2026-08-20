#!/usr/bin/env python3
import sys
import json
import pdfplumber
import pandas as pd
import os

def main():
    if len(sys.argv) < 3:
        print(json.dumps({'error': 'Missing arguments'}))
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        all_tables = []
        with pdfplumber.open(input_path) as pdf:
            for page in pdf.pages:
                tables = page.extract_tables()
                for table in tables:
                    df = pd.DataFrame(table[1:], columns=table[0])
                    all_tables.append(df)

        if not all_tables:
            print(json.dumps({'error': 'В документе не найдено таблиц.'}))
            sys.exit(0)

        # Write all tables to different sheets or one sheet
        with pd.ExcelWriter(output_path, engine='openpyxl') as writer:
            for idx, df in enumerate(all_tables):
                sheet_name = f'Таблица_{idx+1}'
                df.to_excel(writer, sheet_name=sheet_name, index=False)

        print(json.dumps({'success': True, 'path': output_path}))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
