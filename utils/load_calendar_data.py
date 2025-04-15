#!/usr/bin/python
import sqlalchemy
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from sqlalchemy import Table, MetaData, Column, Integer, String
from sqlalchemy.orm import mapper
import sys, os

dbconf="mysql://swang:VVKKll11@@@localhost/dev?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

def addData(mytable, conn, idx, data):
  print('adding:DATA to', mytable, idx, data)
  ins = mytable.insert()
  ins = mytable.insert().values(idx=idx,data=data)
  ret = conn.execute(ins)

dataPath = '/sites/projects/'
def openData(mytable, conn, dataPath, dataFile):
  f = open(dataPath+dataFile, 'r')
  for idx, data in enumerate(f):
    # print(idx, data.strip())
    addData(mytable, conn, idx, data.strip())
  f.close()

#### main ####
# dataFile = 'ChineseToGregorian.data'
# dataTable = 'cal_chinese_to_gregorians'

# dataFile = 'calendricalSolarTerms.data'
# dataTable = 'cal_calendrical_solar_terms'

# dataFile = 'newMoon.data'
# dataTable = 'cal_new_moons'


# dataFile = 'fullMoons.data'
# dataTable = 'cal_full_moons'

# dataFile = 'thirdQuarters.data'
# dataTable = 'cal_third_quarters'

dataFile = 'solarTerms.data'
dataTable = 'cal_solar_terms'
mytable = Table(dataTable, MetaData(),
  Column('id', Integer, primary_key=True),
  Column('idx', Integer),
  Column('data', String)
)
conn = engine.connect()
openData(mytable, conn, dataPath, dataFile)