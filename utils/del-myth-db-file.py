#!/usr/bin/python3
import sys, os, re
# from pathlib import Path
from datetime import datetime, timedelta
import sqlalchemy
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from datetime import datetime, date
import time
# sys.path.append('/home/swang/projects/utils')
from Models import recorded, channel

# print('==== starting del-myth-db-file.py -- using sqlchemy version', sqlalchemy.__version__, sys.path)
print('==== starting del-myth-db-file.py -- using sqlchemy version', sqlalchemy.__version__)
if len(sys.argv) < 2:
    print('Please provide filename to be removed')
    sys.exit(101)
fileToBeRemoved = sys.argv[1]
if os.path.isfile(fileToBeRemoved):
    print('file %s will be removed from file system'%fileToBeRemoved)
    os.remove(fileToBeRemoved)
else:
    print('[%s] is no a file'%fileToBeRemoved)
    sys.exit(102)

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

xx = fileToBeRemoved.split('/')
basename = xx[len(xx)-1]
basenamex = f'{" "*(len(fileToBeRemoved)-len(basename)-2)}' + basename
print('record %s will be removed from table recorded'%basenamex)
session.query(recorded).filter(recorded.basename == basename).delete(synchronize_session=False)

sys.exit(0)
