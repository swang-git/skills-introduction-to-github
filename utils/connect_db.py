#!/bin/python
import sys, os
from os.path import dirname
# sys.path.append(os.path.join('/sites/projects/utils'))
from mysql_dbconfig import read_db_config
# print(sys.path)
db = read_db_config()
print(db)

