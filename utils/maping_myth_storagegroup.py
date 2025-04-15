#!/usr/bin/python3
# from os import listdir
# from os.path import isfile, join
import glob

import sqlalchemy
from datetime import datetime
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models import recorded

print('==== starting ----')

dbconf="mysql://mythtv:mythtv@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

files = glob.glob("/tv/rec/*.ts")
# print(files, len(files))
def checking_rec(files):
  for f in files:
    basename = f[8:100]
    for starttime, title, recgroup, storagegroup \
      in session.query(recorded.starttime, recorded.title, recorded.recgroup, recorded.storagegroup).filter(recorded.basename==basename).order_by(recorded.starttime):
      # gap = '\t\t'
      if (len(title) > 60): gap = '\t'
      # elif (len(title) > 50): gap = '\t\t'
      # elif (len(title) > 40): gap = '\t\t\t'
      # elif (len(title) > 30): gap = '\t\t\t\t'
      # elif (len(title) > 20): gap = '\t\t\t\t\t'
      # elif (len(title) > 10): gap = '\t\t\t\t\t\t'
      # print('TV NAME:', title, gap, basename, recgroup, storagegroup)
      print('TV NAME:', '{:35}'.format(title), starttime, basename, recgroup, storagegroup)

def updating_rec(files):
  for f in files:
    basename = f[8:100]
    query = session.query(recorded.basename, recorded.recgroup, recorded.storagegroup).filter(recorded.basename==basename)
    for x in query:
      if x is None:
        print('record NOT exists for', basename)
      else:
        print('record exists for', basename==x.basename, basename, x, 'updating to recgroup, storagegroup')
        # query.update({ 'recgroup':'MythTV', 'storagegroup':'MythTV' })
        # session.commit()

######## main ########
checking_rec(files)
# updating_rec(files)