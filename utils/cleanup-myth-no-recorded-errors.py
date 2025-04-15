#!/usr/bin/python3
# from os import listdir
# from os.path import isfile, join
import glob
import sys, os.path
import re

import sqlalchemy
from os.path import exists
from os.path import getmtime
from datetime import datetime
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models import recorded


import argparse
parser = argparse.ArgumentParser(description='check and clean up recorded tables if record files are not existing')
parser.add_argument('-f', '--filename', nargs='?', metavar='filename', type=str,  default='20221015222918.1835', help='mythtv log filename like "/var/log/mythtv/mythbackend.20221015222918.1835.log"')
parser.add_argument('-m', '--missingf', nargs='?', metavar='missingf', type=bool, default=False, help='show missing files in mythtv log file')
parser.add_argument('-c', '--cleandbx', nargs='?', metavar='cleandbx', type=bool, default=False, help='delete the records in the table recorded if files not exist')
parser.add_argument('-d', '--foldernm', nargs='?', metavar='foldernm', type=str,  default='/dtv/rec', help='folder name holding recorded files')
parser.add_argument('-s', '--showfile', nargs='?', metavar='showfile', type=bool, default=False, help='show recorded files not in the database')
parser.add_argument('-r', '--showrecd', nargs='?', metavar='showrecd', type=bool, default=False, help='show records in the database without recorded file')
parser.add_argument('-n', '--cleanrec', nargs='?', metavar='cleanrec', type=bool, default=False, help='cleanup records in the database if the recorded file is missing')
args = parser.parse_args()
filename = args.filename
missingf = args.missingf
cleandbx = args.cleandbx
foldernm = args.foldernm
showfile = args.showfile
showrecd = args.showrecd
cleanrec = args.cleanrec
# print(args); sys.exit(0)

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

def checking_rec():
  print('-fn-checking_rec')
  files = sorted(glob.glob(foldernm + "/*.ts"), key=getmtime, reverse=True)
  # print(files, len(files)); sys.exit()
  # for idx, f in enumerate(files):
  idx = 0
  for f in files:
    basename = re.sub(r'(.*)\/(\d{4,5}_\d{14}).ts', '\\2.ts', f)
    qa = session.query(recorded.starttime, recorded.title).filter(recorded.basename==basename)
    if qa.scalar() and showrecd: 
      title = '{:45}'.format(qa[0].title)
      starttime =  str(qa[0].starttime)[:16]
      idx += 1
      print(idx, title, starttime, basename)
    elif qa.scalar() == None and showfile:
      idx += 1
      fsz = getFileSizeG(f)
      print(idx, 'file', f, fsz, 'not in the database')

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

def fileNotExist(basename):
  # if not exists('/stv/' + basename) && not exists('/htv/myth/' + basename) && not exists('/etv/rec/' + basename) && not exists('/home/swang/mtv/' + basename): return True
  # if exists('/stv/' + basename) or exists('/htv/myth/' + basename) or exists('/etv/rec/' + basename) or exists('/home/swang/mtv/' + basename):
  if exists('/btv/' + basename) or exists('/ctv/' + basename) or exists('/dtv/rec/' + basename) or exists('/f36/bak/xtv/' + basename):
    # print('XXXXX file exists', basename)
    return False
  return True
import re
def cleanup_recorded_error(path):
  # print(path)
  i = 0
  dat = open(path, 'r')
  bnList = []
  for line in dat:
    line = line.strip()
    if line == '': continue
    i += 1
    # print(i, '\t', line)
    # p = re.compile('Database metadata will not be removed')
    p = re.compile(r'\d_\d\.ts')
    bs = p.findall(line)
    if len(bs) < 1: continue
    basename = bs[0]
    # print(basename)
    # print(bs, line)
    if basename not in bnList and fileNotExist(basename): bnList.append(basename)
  check_db(bnList)

def check_db(bnList):
  # print('Total files in', foldernm, len(bnList))
  print("-fn- check_db", bnList)
  for basename in bnList:
    query = session.query(recorded.starttime, recorded.title, recorded.recgroup, recorded.storagegroup).filter(recorded.basename==basename)
    for starttime, title, recgroup, storagegroup in query:
      if recgroup == 'Deleted':
        # print('DELETED', basename, title, recgroup, storagegroup)
        print('DELETED and NoFile', basename)
        # print('"' + basename + '",')  # for clean up database
        # query.delete(synchronize_session=False)
      else:
        print('ACTIVE', basename, title, recgroup, storagegroup)
      # query.delete(synchronize_session=False)
def getFileSizeG(f):
  return "{:.1f}".format(os.path.getsize(f)/1024/1024/1024) + 'G'

def check_file_sorted():
  # files = sorted(glob.glob(foldernm + '/*.ts'), key=getmtime, reverse=True)
  files = sorted(glob.glob(foldernm + '/*.ts'), key=os.path.getsize, reverse=False)
  for idx, f in enumerate(files):
    fsz = "{:.1f}".format(os.path.getsize(f)/1024/1024/1024) + 'G'
    # fsz = "{:.1f}".format(os.path.getsize(f)/1024/1024) + 'M'
    print(idx, f, fsz)

def cleanup_recorded_table():
   query = session.query(recorded.starttime, recorded.title, recorded.recgroup, recorded.storagegroup, recorded.basename).order_by(recorded.starttime)
   for rec in query:
    if fileNotExist(rec.basename):
      print(rec.starttime, rec.recgroup, rec.basename, rec.title)

######## main ########
path = '/var/log/mythtv/mythbackend.' + filename + '.log'
print("=*= main =*= checking folder:{} showfile:{} filename:{}".format(foldernm, showfile, filename))
# if filename: 
#   print("cleanup_recorded_error find missing recorded file in {})".format(path))
#   cleanup_recorded_error(path)
# if foldernm and (showfile or showrecd): checking_rec()
# updating_rec(files)
# if foldernm: check_file_sorted()
if cleanrec:
  print("-fn- cleanup_recorded_table() -- cleanup records in table recorded if recorded files missing")
  cleanup_recorded_table()
