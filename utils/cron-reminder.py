#!/Users/swang/myenv/bin/python3
import os, sys
import sqlalchemy
from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import reminders
import datetime

# print('==== cron-reminder.py -- using sqlchemy version', sqlalchemy.__version__)

dbconf="mysql+pymysql://swang:VVKKll11##@localhost/prod?charset=utf8mb4"
##dbconf="mysql://swang:VVKKll11##@localhost/prod?charset=utf8mb4"
##engine = create_engine(dbconf, encoding='utf8', echo=False)
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

# q = session.query(reminders).filter(text("user_id = 1 and status = 'A' and due_date between now() and date_add(now(), interval 3 day)"))
# print('query', q)
today = datetime.date.today()
result1 = session.execute(text("SELECT * FROM reminders WHERE status = 'A' and due_date between :today and date_add(:today, interval 7 day) and user_id=:userId"), {'today':today, 'userId':1} )
result2 = session.execute(text("SELECT * FROM spends WHERE status = 'A' and cat_id = 2 and subcat_id = 4 and purchasedon between :today and date_add(:today, interval 7 day)"), {'today':today} )

for rec in result1: print('due_date:', rec.due_date, result1.rowcount)
for rec in result2: print('teetime:', rec.purchasedon, result2.rowcount)

result1.close()
result2.close()
sys.exit(0)


def openIt():
#     exit_code = os.WEXITSTATUS(os.system('xdg-open "http://71.59.72.103/QV1/reminder/list"'))
#    exit_code = os.WEXITSTATUS(os.system('xdg-open "http://71.59.72.103/reminder"'))
    exit_code = os.WEXITSTATUS(os.system('xdg-open "http://prod/apps/reminder"'))
#     exit_code = os.WEXITSTATUS(os.system('kioclient5 exec "http://prod/reminder"'))
    if exit_code != 0:
        # print('xdg-open failed, exit ...\n')
        print('kioclient5 exec failed, exit ...\n')
        sys.exit(-1)

if result1.rowcount > 0 or result2.rowcount > 0:
        openIt()
else: print('---- Nothing to do in the next 7 days from ', datetime.datetime.now())
result1.close()
result2.close()
sys.exit(0)
