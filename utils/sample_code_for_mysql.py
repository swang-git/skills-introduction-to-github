#!/bin/python
# from sqlalchemy.dialects.mysql import VARCHAR, TEXT
# from sqlalchemy.orm import sessionmaker
# from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, create_engine
# from sqlalchemy.ext.declarative import declarative_base
import sys
import mysql.connector
from mysql.connector import Error
from datetime import date, timedelta
import argparse
from mysql.connector import MySQLConnection, Error
from mysql_dbconfig import read_db_config


def my_argparse():
    parser = argparse.ArgumentParser(
        description='calc glucose checking data for the last "interdays" days start from "sdays"')
    parser.add_argument("database", metavar='str', type=str, nargs='?',
                        default='prod', help='database name, default to prod')
    parser.add_argument("sdays", metavar='int', type=int, nargs="?", default=0,
                        help='0 (default) for starting today or 1 for starting frrom yesterday, etc')
    parser.add_argument("interdays", metavar='int', type=int, nargs='?',
                        default=90, help='how many days back from sdays, default to 90 days')
    parser.add_argument("fasting", metavar='str', type=str, nargs='?',
                        default='fasting', help='calc fasting only or all, default to "fasting"')
    return parser.parse_args()


def connect():
    """ Connect to MySQL database """

    db_config = read_db_config()
    conn = None
    try:
        print('Connecting to MySQL database...')
        conn = MySQLConnection(**db_config)

        if conn.is_connected():
            print('Connection established.', conn.cursor)
        else:
            print('Connection failed.')

    except Error as error:
        print(error)

    # finally:
    #     if conn is not None and conn.is_connected():
    #         conn.close()
    #         print('Connection closed.')

    return conn

def XX_connect(db):
    """ Connect to MySQL database """
    conn = None
    try:
        conn = mysql.connector.connect(
            host='localhost', database=db, user='XXXXX', password='*******')
        # conn = mysql.connector.connect(dbstr)
        if conn.is_connected():
            print('Connected to MySQL database: %s' % db)
            return conn

    except Error as e:
        print(e)

    # finally:
        # if conn is not None and conn.is_connected():
        # conn.close()


def calc_a1c(conn, sdays, interdays, fasting):
    today = date.today()
    start_date = (today - timedelta(days=sdays + interdays)
                  ).strftime('%Y-%m-%d')
    end_date = today - timedelta(days=sdays)
    end_dateq = (today - timedelta(days=sdays-1)).strftime('%Y-%m-%d')
    calc_info = "calc all"
    if fasting:
        calc_info = "calc fasting only"
    print('days from today:{0}, days in between:{1}, From Date:{2} To Date:{3}, {4}'.format(
        sdays, interdays, start_date, end_date, calc_info))
    cursor = conn.cursor()
    # query = 'SELECT datetime, fasting FROM glucose_checks WHERE datetime >= "%s" and DATE_FORMAT(datetime, "%Y-%m-%d") <="%s"' % (start_date.strftime('%Y-%m-%d'),end_date.strftime('%Y-%m-%d'))
    # query = 'SELECT datetime, fasting FROM glucose_checks WHERE datetime >= "%s" and datetime <="%s"' % (start_date, end_dateq)
    queryFas = 'SELECT datetime, fasting FROM glucose_checks WHERE food is null AND datetime >= "%s" AND datetime <="%s"' % (
        start_date, end_dateq)
    queryAll = 'SELECT datetime, fasting FROM glucose_checks WHERE datetime >= "%s" and datetime <="%s"' % (
        start_date, end_dateq)
    # print('query=%s' % query)
    if fasting:
        cursor.execute(queryFas)
    else:
        cursor.execute(queryAll)
    # cursor.execute('SELECT datetime, fasting FROM glucose_checks WHERE date_format(datetime, "%Y-%m-%d") >= "%s" and datetime <="%s"' % (start_date, end_dateq))
    rows = cursor.fetchall()
    lst = []
    for row in rows:
        lst.append(row[1])
    # print(lst)
    vsum = sum(lst)
    vlen = len(lst)
    eag = vsum / vlen
    a1cp = (eag + 46.7) / 28.7
    a1c = 10.929 * (a1cp - 2.15)
    cea = eag / 18.015
    print('sum=%d, cnt=%d, eag=%s, a1cp=%s%s, a1c=%s cea=%s' % (
        vsum, vlen, round(eag, 1), round(a1cp, 1), '%', round(a1c, 1), round(cea, 1)))


if __name__ == '__main__':
    parser = my_argparse()
    allorFasting = parser.fasting
    sdays = parser.sdays
    interdays = parser.interdays
    database = parser.database
    # conn = connect(database)
    conn = connect()
    fasting = True
    if allorFasting == 'all':
        fasting = False
    calc_a1c(conn, sdays, interdays, fasting)


sys.exit(0)


def connect():
    """ Connect to MySQL database """

    db_config = read_db_config()
    conn = None
    try:
        print('Connecting to MySQL database...')
        conn = MySQLConnection(**db_config)

        if conn.is_connected():
            print('Connection established.')
        else:
            print('Connection failed.')

    except Error as error:
        print(error)

    finally:
        if conn is not None and conn.is_connected():
            conn.close()
            print('Connection closed.')


if __name__ == '__main__':
    connect()

sys.exit(0)

######################################

dbconf = "mysql://swang:VVKKll11##@localhost/devx?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)

Session = sessionmaker(bind=engine)
session = Session()

Base = declarative_base()


class GlucoseCheck(Base):
    __tablename__ = 'glucose_checks'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    datetime = Column(DateTime)
    fasting = Column(Integer)
    af2hour = Column(Integer)
    food = Column(String(256))
    drink = Column(String(32))
    note = Column(String(256))
    fruit = Column(String(45))
    status = Column(CHAR(1))

    def __init__(self, ins):
        self.user_id = ins.user_id
        self.datetime = ins.datetime
        self.fasting = ins.fasting
        self.af2hour = ins.af2hour
        self.food = ins.food
        self.fruit = ins.fruit
        self.note = ins.note
        self.drink = ins.drink
        self.status = ins.status
        self.status = 'A'


def get_fasting(numDays):
    today = date.today()
    print("Today's date:", today)
    days_before = today - timedelta(days=numDays-1)
    print(numDays, "days before today:", days_before)
    # for idx, rec in enumerate(session.query(GlucoseCheck).filter(GlucoseCheck.datetime > days_before).order_by(GlucoseCheck.datetime)):
    query = session.query(GlucoseCheck).filter(
        GlucoseCheck.datetime > days_before).order_by(GlucoseCheck.datetime)
    for idx, rec in enumerate(query):
        print(idx, rec.datetime, rec.fasting, query.count())
        print(query.fasting)


print('--- Last-90-date A1c Calc begins ---')
get_fasting(2)
print('--- finished ---')
