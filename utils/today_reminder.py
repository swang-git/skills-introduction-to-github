#!/usr/bin/python3
import os, sys
import sqlalchemy
from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import reminders
import datetime

# print('==== cron-reminder.py -- using sqlchemy version', sqlalchemy.__version__)

dbconf="mysql://swang:VVKKll11##@localhost/prod?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

today = datetime.date.today()
print('today', today)
ret = session.execute( "SELECT * FROM reminders WHERE status = 'A' and due_date between now() and date_add(now(), interval 17 day) and user_id=:param", {"param":1} )
# ret = session.execute( "SELECT * FROM reminders WHERE status = 'A' and due_date = date_format(now(), '%Y-%m-%d') and user_id=:param", {"param":1} )
#ret = session.execute("SELECT * FROM reminders WHERE status = 'A' and due_date=:theDate and user_id=:userId", {'theDate':today, 'userId':1})

def setNextReminder():
	# print('exec setNextReminder()')
	rec = ret.fetchone()
	print('due_date:', rec.due_date, rec.recursive, rec.tag)
	newDate = today + datetime.timedelta(days=rec.recursive)
	newRet = session.execute("SELECT * FROM reminders WHERE status = 'A' and due_date=:theDate and user_id=:userId and tag=:typ", {'theDate':newDate, 'userId':1, 'typ':rec.tag})
	rows = newRet.fetchall()
	if (len(rows) > 0):
		row = rows[0]
		print('check -- It has already been set for next reminder of', row.id, row.due_date, row.tag, 'return ....', len(rows))
		newRet.close()
	else:
		rmd = reminders(rec)
		rmd.due_date = newDate
		print('++++ add new reminder add', rmd.due_date, rmd.tag)
		session.add(rmd)
		session.commit()

if ret.rowcount >= 1:
  setNextReminder()
else: print('---- Nothing to do for today: ', today)
ret.close()
sys.exit(0)
