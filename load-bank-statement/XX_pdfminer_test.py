#!/usr/bin/python
from pdfminer.converter import PDFPageAggregator
from pdfminer.layout import LAParams, LTFigure, LTTextBox
from pdfminer.pdfdocument import PDFDocument
from pdfminer.pdfinterp import PDFPageInterpreter, PDFResourceManager
from pdfminer.pdfpage import PDFPage, PDFTextExtractionNotAllowed
from pdfminer.pdfparser import PDFParser

pdf_path = '/sites/webdata/docs/Chase/202005.pdf'
text = ""
stack = []
with open(pdf_path, 'rb') as f:
    parser = PDFParser(f)
    doc = PDFDocument(parser)
    for page in list(PDFPage.create_pages(doc)):
      rsrcmgr = PDFResourceManager()
      device = PDFPageAggregator(rsrcmgr, laparams=LAParams())
      interpreter = PDFPageInterpreter(rsrcmgr, device)
      interpreter.process_page(page)
      layout = device.get_result()

      for obj in layout:
        if isinstance(obj, LTTextBox):
            text += obj.get_text()

        elif isinstance(obj, LTFigure):
            stack += list(obj)
print(text)