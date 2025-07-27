#!/usr/bin/python
# extracting_text.py
from PyPDF2 import PdfFileReader

path = '/sites/webdata/docs/Chase/202001.pdf'
def text_extractor(path):
    with open(path, 'rb') as f:
        pdf = PdfFileReader(f)

        # get the first page
        page = pdf.getPage(1)
        print(page)
        print('Page type: {}'.format(str(type(page))))

        text = page.extractText()
        print(text)


if __name__ == '__main__':
    # path = 'reportlab-sample.pdf'
    text_extractor(path)