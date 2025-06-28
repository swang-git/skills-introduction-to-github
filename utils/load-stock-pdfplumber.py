#!/Users/swang/myenv/bin/python
import pdfplumber

with pdfplumber.open("/Users/swang/sites/webdata/docs/stocks/20250628_MSFT.pdf") as pdf:
    for page in pdf.pages:
        print(page.extract_text())  # Better text extraction
        print(page.extract_table())  # Extracts tables as lists
