#!/usr/bin/python
import os
# from collections import namedtupleimport sqlalchemy
from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import recorded, channel_ALL, channel

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

print('======= the following record(s) deleted from recorded table =======')
path = '/home/swang/vlnk'
# vlnks = os.listdir(path)
# print(vlnks)
lnks = {}
for (root, dirs, files) in os.walk(path, topdown=False, onerror=False, followlinks=True):
  for vfile in files:
    linkto = os.readlink(vfile)
    basename = linkto.split('/')[4]
    # lnks[basename] = vfile.replace('.ts', '').replace('》', '').replace('《', '')
    # lnks[basename] = linkto + " -> " + vfile.replace('.ts', '').replace('》', '').replace('《', '')
    lnks[basename] = [linkto, vfile]

# lnks['11001_20240428143000.tx'] = 'XXXXXXXXXXXXX XXXX VVVV'
for (key, value) in lnks.items():
  # rec = session.query(recorded).filter(recorded.basename==key).first()
  sql = text("SELECT title FROM recorded WHERE basename = :x").bindparams(x=key)
  rec = session.execute(sql).first()
  # print(rec)
  dfile = ''
  if (os.path.exists(value[0]) == False): dfile += value[0] 
  if (rec == None): dfile += ' --> ' + value[1]
  if (dfile != ''): print(dfile)




