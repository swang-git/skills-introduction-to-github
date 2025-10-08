#!/bin/python
import sys
if len(sys.argv) < 1: yr = 1924
else: yr = int(sys.argv[1])
# print(sys.argv[1])

def get_jiazi_year (yr):
    if yr == 1924: return yr
    i = 0
    while True:
        if yr < 1924:
            a = 1924 - (i + 1) * 60
            b = 1924 - i * 60
            if a <= yr < b: return a
        elif yr > 1924:
            a = 1924 + i * 60
            b = 1924 + (i + 1) * 60
            if a <= yr < b: return a
        i += 1

y = get_jiazi_year(yr)
print("公历\t农历\t生肖 对照表")
tiangan = ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸']
dizhi = ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥']
sxiao = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪']
# y = 1864
d = 0
s = 0
for t in range(60):
    print("%s\t%s\t%s"%(y, tiangan[t%10]+dizhi[d%12],sxiao[s%12]))
    y += 1
    t += 1
    d += 1
    s += 1
