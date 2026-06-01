#!/Users/swang/myenv/bin/python3
import os, sys
import sqlalchemy
from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import reminders
import datetime

# print('==== cron-reminder.py -- using sqlchemy version', sqlalchemy.__version__)

##dbconf="mysql://swang:VVKKll11##@localhost/prod?charset=utf8mb4"
dbconf="mysql+pymysql://swang:Ybsjll11@localhost/prod?charset=utf8mb4"
##engine = create_engine(dbconf, encoding='utf8', echo=False)
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

# q = session.query(reminders).filter(text("user_id = 1 and status = 'A' and due_date between now() and date_add(now(), interval 3 day)"))
# print('query', q)

today = datetime.date.today()


sql1 = f"SELECT * FROM memos WHERE status = 'A' and date between '{today}' and date_add('{today}', interval 7 day)"
# result1 = session.execute(text("SELECT * FROM memos WHERE status = 'A' and date between :today and date_add(:today, interval 7 day)"), {'today':today} )
result1 = session.execute(text(sql1))

# "SELECT * FROM reminders WHERE status = 'A' and due_date between :today and date_add(:today, interval 7 day) and user_id=:userId"), {'today':today, 'userId':1} )
sql2 = "SELECT * FROM reminders WHERE status = 'A' and due_date between '{today}' and date_add('{today}', interval 7 day) and user_id=1"
result2 = session.execute(text(sql2))

# "SELECT * FROM spends WHERE status = 'A' and cat_id = 2 and subcat_id = 4 and purchasedon between :today and date_add(:today, interval 7 day)"), {'today':today} )
sql3 = f"SELECT * FROM spends WHERE status = 'A' and cat_id = 2 and subcat_id = 4 and purchasedon between '{today}' and date_add('{today}', interval 7 day)"
result3 = session.execute(text(sql3))

for rec in result1: print('date:', rec.date, result1.rowcount)
for rec in result2: print('due_date:', rec.due_date, result2.rowcount)
for rec in result3: print('teetime:', rec.purchasedon, result3.rowcount)

def openIt():
    exit_code = os.WEXITSTATUS(os.system('open "http://prod/apps/reminder"'))
    if exit_code != 0:
        # print('xdg-open failed, exit ...\n')
        print('kioclient5 exec failed, exit ...\n')
        sys.exit(-1)

if result1.rowcount > 0 or result2.rowcount > 0 or result3.rowcount > 0: openIt()
else: print('---- Nothing to do in the next 7 days from ', datetime.datetime.now())
result1.close()
result2.close()
result3.close()
sys.exit(0)
