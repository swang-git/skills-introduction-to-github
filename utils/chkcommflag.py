#!/usr/bin/python
from glob import glob
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models import recorded

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

print('======= starting =======')
dirname = '/var/log/mythtv/'
commfiles = [f for f in glob(dirname + 'mythcommflag.*.log')]
infolines = []
for fnm in commfiles:
  with open(fnm, 'r') as f:
    for line in f:
      if 'Opening' in line: infolines.append(line)

for line in infolines: 
  # print(line)
  x = line.split(' ')
  a1 = x[0] + ' ' + x[1]; a2 = x[len(x) - 1]
  tim = a1.split('.')[0]
  x1 = a2.strip().replace('"', '').split('/')
  bsn = x1[len(x1) - 1].replace('\'', '')
  tit = None
  for rec in session.query(recorded).filter(recorded.basename == bsn): tit = rec.title
  if tit != None:
    a = [tim, bsn, tit]
    print(' '.join(a))