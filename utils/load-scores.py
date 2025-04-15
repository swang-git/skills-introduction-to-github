#!/usr/bin/python3
from pyexcel_ods import get_data
import json
# from json import JSONEncoder
from datetime import date
from pprint import pprint


infile = "/home/swang/Documents/PGCClubScores2018.ods"

def datetime_handler(self, x):
    if isinstance(x, datetime.datetime):
        return x.isoformat()
    raise TypeError("Unknown type")
    # return json.JSONEncoder.default(self, x)

def xdefault(self, o):
   try:
       iterable = iter(o)
   except TypeError:
       pass
   else:
       return list(iterable)
   # Let the base class default method raise the TypeError
   return json.JSONEncoder.default(self, o)

class DatetimeEncoder(json.JSONEncoder):
    def default(self, obj):
        # if isinstance(obj, datetime):
        #     return obj.strftime('%Y-%m-%dT%H:%M:%SZ')
        if isinstance(obj, date):
            return obj.strftime('%Y-%m-%d')
        # Let the base class default method raise the TypeError
        return json.JSONEncoder.default(self, obj)

def dump_scores_to_file():
    data = get_data(infile)
    print(json.dumps(data, cls=DatetimeEncoder))
# dump_scores_to_file()

def load_score_sheet():
    # sheet = json.loads(json.loads("Documents/2018_scores_sheet.json"))
    # sheet['scores']
    jsondata = open("Documents/2018_scores_sheet.json", 'r').read()
    score_sheet = json.loads(jsondata)
    # print(score_sheet['scores'][0][4])
    # print(score_sheet['scores'][1][4])
    # print(score_sheet['scores'][2][4])
    # print(score_sheet['scores'][3][4])
    # print(score_sheet['scores'][4][4])
    # print(score_sheet['scores'][6][6])
    # print(score_sheet['scores'][7][6])
    # pprint(score_sheet)
    return score_sheet['scores']

def checking_match2018_4(sheet):
    # for i in range(10, 20):
    #     print(i, sheet[i][0], sheet[i][1], sheet[i][2], sheet[i][3], sheet[i][4], sheet[i][5]) 

    # for i in range(38, 44): # 2018-08-19
    #     print(i, sheet[5][0], sheet[5][i])
    
    for i in range(5, 61): # 2018-08-19
        if i == 23: continue
        print(i, sheet[i][0], sheet[i][38], sheet[i][39], sheet[i][40], sheet[i][41], sheet[i][42], sheet[i][43]) #, sheet[i][44])

    print(23, sheet[23][0], sheet[23][38], sheet[23][39], sheet[23][40], sheet[23][41], sheet[23][42], sheet[23][42]) #, sheet[23][44])
    print(sheet[23])

def checking_match2018_3(sheet):
    for i in range(5, 61): # 2018-08-19
        if i == 23 or i == 34: continue
        print(i, sheet[i][0], sheet[i][44], sheet[i][45], sheet[i][46], sheet[i][47], sheet[i][48], sheet[i][49])

    # print(23, sheet[23][0], sheet[23][38], sheet[23][39], sheet[23][40], sheet[23][41], sheet[23][42], sheet[23][42]) #, sheet[23][44])
    # print(sheet[23])

def checking_match2018_2(sheet):
    for i in range(5, 61): # 2018-08-19
        if i == 8 or i == 23 or i == 34 or i == 40 or i == 48 or i == 53: continue
        print(i, sheet[i][0], sheet[i][50], sheet[i][51], sheet[i][52], sheet[i][53], sheet[i][54], sheet[i][55])

    # print(23, sheet[23][0], sheet[23][38], sheet[23][39], sheet[23][40], sheet[23][41], sheet[23][42], sheet[23][42]) #, sheet[23][44])
    # print(sheet[23])

def checking_match2018_1(sheet):
    for i in range(5, 61): # 2018-08-19
        if i == 8 or i == 9 or i == 15 or i == 16 or i == 22 or i == 23 or i == 27 or i == 31 or i == 32 or i==34 or i==40 or i==48 or i==53 or i==56: continue
        print(i, sheet[i][0], sheet[i][56], sheet[i][57], sheet[i][58], sheet[i][59], sheet[i][60], sheet[i][61])

def checking_match2016_1(sheet):
    for i in range(5, 61): # 2018-08-19
        # if i == 8 or i == 9 or i == 15 or i == 16 or i == 22 or i == 23 or i == 27 or i == 31 or i == 32 or i==34 or i==40 or i==48 or i==53 or i==56: continue
        # print(i, sheet[i][0], sheet[i][117], sheet[i][117], sheet[i][118], sheet[i][119], sheet[i][120], sheet[i][121])
        print(i, sheet[i][0], sheet[i][116], sheet[i][117], sheet[i][118], sheet[i][119], sheet[i][120], sheet[i][121])
def checking_match2016_2(sheet):
    for i in range(5, 61): # 2018-08-19
        # if i == 8 or i == 9 or i == 15 or i == 16 or i == 22 or i == 23 or i == 27 or i == 31 or i == 32 or i==34 or i==40 or i==48 or i==53 or i==56: continue
        # print(i, sheet[i][0], sheet[i][117], sheet[i][117], sheet[i][118], sheet[i][119], sheet[i][120], sheet[i][121])
        print(i, sheet[i][0], sheet[i][110], sheet[i][111], sheet[i][112], sheet[i][113], sheet[i][114], sheet[i][115])

def checking_match2016_3(sheet):
    for i in range(5, 61): # 2018-08-19
        # if i == 8 or i == 9 or i == 15 or i == 16 or i == 22 or i == 23 or i == 27 or i == 31 or i == 32 or i==34 or i==40 or i==48 or i==53 or i==56: continue
        # print(i, sheet[i][0], sheet[i][117], sheet[i][117], sheet[i][118], sheet[i][119], sheet[i][120], sheet[i][121])
        print(i, sheet[i][0], sheet[i][104], sheet[i][105], sheet[i][106], sheet[i][107], sheet[i][108], sheet[i][109])


####################################
def checking_match2016_4(sheet):
    for i in range(5, 61): # 2018-08-19
        if i==6 or i==8 or i==9 or i==15 or i==16 or i==22 or i==23 or i == 26 or i==27 or i==28 or i==30 or i==31 or i==32 or i==33: continue
        print(i, sheet[i][0], sheet[i][98], sheet[i][99], sheet[i][100], sheet[i][101], sheet[i][102], sheet[i][103])

# sheet = load_scores()


def tmnt2016_4(): return range(98, 104)
def tmnt2016_3(): return range(104, 110)
def tmnt2016_2(): return range(110, 116)
def tmnt2016_1(): return range(116, 122)
# for i in tmnt2016_1(): print(i)
class Score:
    """ holding club tournament scores """
    game_name = None
    gross_score = None
    idx_diff = None
    club_idx = None
    net_score = None
    gross_rank = None
    net_rank = None
    player = None
    playerId = -1
    tplayerId = -1

    def isTplayer(self):
        return self.gross_score != None or self.idx_diff != None or self.club_idx != None or self.net_score != None or self.gross_rank != None or self.net_rank != None
    def showScore(self):
        # print('gross_score:',self.gross_score)
        print(self.player, self.isTplayer(), self.gross_score, self.idx_diff, self.club_idx, self.net_score, self.gross_rank, self.net_rank)
# x = Score()
# print(x.isTplayer())
# x.gross_score = 78
# print(x.isTplayer())
# x.showScore()

# sheet = load_scores()
def get_cell_val(sheet, row, col):
    try:
        val = sheet[row][col]
        if val == '': val = None
    except IndexError:
        val = None
    return val

# print(sheet[23][0])
# print(get_cell_val(sheet, 23, 38))
# print(get_cell_val(sheet, 23, 39))
# print(get_cell_val(sheet, 23, 40))
# print(get_cell_val(sheet, 23, 41))
# print(get_cell_val(sheet, 23, 42))
# print(get_cell_val(sheet, 23, 43))

def fmt_float(x):
    if type(x) == float: return round(x, 1)
    else: return x
    # if type(x) == int: return round(x * 1.0, 1)
    # elif type(x) == int: return x

def load_scores(sheet, col_start):
    # rang = tmnt2016_4()
    scores = []
    for row in range(5, 61):
        score = Score()

        score.player = get_cell_val(sheet, row, 0)

        score.gross_score =           get_cell_val(sheet, row, col_start)
        score.idx_diff    = fmt_float(get_cell_val(sheet, row, col_start + 1))
        score.club_idx    = fmt_float(get_cell_val(sheet, row, col_start + 2))
        score.net_score   = fmt_float(get_cell_val(sheet, row, col_start + 3))
        score.gross_rank  = fmt_float(get_cell_val(sheet, row, col_start + 4))
        score.net_rank    = fmt_float(get_cell_val(sheet, row, col_start + 5))

        # x = get_cell_val(sheet, row, col_start + 3)
        # if type(x) == float: score.net_score = round(x, 1)
        
        # x = get_cell_val(sheet, row, col_start + 4)
        # if type(x) == float: score.gross_rank = round(x, 1)
        
        # score.net_rank = get_cell_val(sheet, row, col_start + 5)
        # # if type(x) == float: score.net_rank = x #round(x, 1)

        scores.append(score)
    return scores

def showScores(tmnt, scores):
    print(tmnt)
    for x in scores: x.showScore()

#### main function ####

dump_scores_to_file()

tmnt2016_1 = 116
tmnt2016_2 = 110
tmnt2016_3 = 104
tmnt2016_4 = 98

sheet = load_score_sheet()
# scores = load_scores(sheet, tmnt2016_1)
# scores = load_scores(sheet, tmnt2016_2)
# scores = load_scores(sheet, tmnt2016_3)
# scores = load_scores(sheet, tmnt2016_4)

tmnt2017_1 = 86
tmnt2017_2 = 80
tmnt2017_3 = 74
tmnt2017_4 = 68
# scores = load_scores(sheet, tmnt2017_1)
# scores = load_scores(sheet, tmnt2017_2)
# scores = load_scores(sheet, tmnt2017_3)
# scores = load_scores(sheet, tmnt2017_4)

tmnt2018_1 = 56
tmnt2018_2 = 50
tmnt2018_3 = 44
tmnt2018_4 = 38
# scores = load_scores(sheet, tmnt2018_1); showScores('tmnt2018_1', scores)
# scores = load_scores(sheet, tmnt2018_2); showScores('tmnt2018_2', scores)
# scores = load_scores(sheet, tmnt2018_3); showScores('tmnt2018_3', scores)
scores = load_scores(sheet, tmnt2018_4); showScores('tmnt2018_4', scores)

# checking_match2016_4(sheet)




