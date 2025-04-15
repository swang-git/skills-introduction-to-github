#!/usr/bin/python3
import sqlalchemy
from datetime import datetime
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models_check_gift_card_balance import GCard as GC
from Models_check_gift_card_balance import Spend as SP
import sys, os
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
# from Utils import dlout

# print('os.path.dirname(sys.path[0])', os.path.dirname(sys.path[0]), sys.path[0])
# print('sys.path[0]', sys.path[0])
# sys.exit(0)

if len(sys.argv) != 2:
  print(len(sys.argv), sys.argv)
  print('Please provide database name: dev or Fin')
  sys.exit(1)
if (sys.argv[1] == 'dev'): dbconf="mysql://swang:vvkk@localhost/dev?charset=utf8mb4"
elif (sys.argv[1] == 'Fin'): dbconf="mysql://swang:vvkk@localhost/Fin?charset=utf8mb4"
else:
  print('unknown database:', sys.argv[1], 'exit...')
  sys.exit(0)

engine = create_engine(dbconf, encoding='utf8', echo=False)
# engine = create_engine("mysql://swang:vvkk@localhost/devx?charset=utf8", encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

print('=== starting check_gift_card_balance ===')
gcard = session.query(GC.id, GC.spend_id, GC.spend_datetime, GC.balance).filter_by(status='A').order_by(GC.spend_datetime.asc())
# spend = session.query(SP, GC).filter(SP.id == GC.spend_id, SP.status=='A', GC.status=='A').order_by(SP.purchasedon.asc())
# print(gcard)
# print(spend)
# print(gcard[0].spend_datetime)
# for g in gcard: print(g.id, g.spend_id, g.spend_datetime, g.balance)

gcSpendIds = [ gc.spend_id for gc in gcard ]
# print(gcSpendIds)
# print(gcard.count())
i = -1
for g in gcard:
  i += 1
  if i > gcard.count() - 1: break
  gid = gcSpendIds[i]
  # print(gid)
  spend = session.query(SP.totalpaid, SP.purchasedon).filter_by(status='A', id=gcSpendIds[i]).all()[0]
  # print(spend[0])
  cost = spend.totalpaid
  check = gcard[i-1].balance - cost
  if i == 0: check = '500.00'
  # dt = g.spend_datetime.strftime("%b %d %Y %H:%M")
  dt = g.spend_datetime.strftime("%Y-%m-%d %H:%M")
  wd = g.spend_datetime.strftime("%A")
  if g.balance == check or i == 0: print(i+1, g.spend_id, dt, g.balance, check, cost, wd)
  else: print("ERROR", i+1, g.spend_id, dt, g.balance, check, cost, wd)


session.close()
