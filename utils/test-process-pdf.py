#!/usr/bin/python
import fitz

filepath = '/home/swang/Documents/fidelity/snapshot_20210122.pdf'
def get_text(filepath: str) -> str:
  with fitz.open(filepath) as doc:
    text = ""
    for page in doc:
      text += page.getText().strip()
    return text
txt = get_text(filepath)
print(txt)
