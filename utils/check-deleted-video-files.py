#!/usr/bin/python
import os, sys
# from collections import namedtupleimport sqlalchemy
from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import recorded, channel_ALL, channel

dbconf="mysql://mythtv:mythtv@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

def log_error(exec):
    print('Skipping directory due to:', exec)

def follow_link(path):
    """Return absolute path to the object a symlink ultimately references."""
    return os.path.realpath(path) if os.path.islink(path) else path

print('======= the following record(s) deleted from recorded table =======')
path = '/home/swang/vlnk'
# vlnks = os.listdir(path)
# print(vlnks)
lnks = {}
for root, dirs, files in os.walk(path, topdown=False, onerror=log_error, followlinks=False):
  for symname in files:
    full = os.path.join(root, symname)
    linked_file = follow_link(full)
    # print(filename)
    # sys.exit(1)
    # linkto = os.readlink(linked_file)
    # basename = linkto.split('/')[4]
    basename = linked_file.split('/')[2]
    # print(basename)
    # sys.exit(1)
    # lnks[basename] = linked_file.replace('.ts', '').replace('》', '').replace('《', '')
    # lnks[basename] = linkto + " -> " + linked_file.replace('.ts', '').replace('》', '').replace('《', '')
    # lnks[basename] = [linkto, linked_file]
    lnks[basename] = [symname, linked_file]

# lnks['11001_20240428143000.tx'] = 'XXXXXXXXXXXXX XXXX VVVV'
for (key, value) in lnks.items():
  # print("key=[%s] value0=%s value1=%s"%(key, value[0], value[1]))
  # sys.exit(1)
  # rec = session.query(recorded).filter(recorded.basename==key).first()
  sql = text("SELECT title FROM recorded WHERE basename = :x").bindparams(x=key)
  rec = session.execute(sql).first()
  # print(rec)
  dfile = ''
  if (os.path.exists(value[0]) == False): dfile += value[0] 
  if (rec == None): dfile += ' --> ' + value[1]
  if (dfile != ''): print(dfile)




