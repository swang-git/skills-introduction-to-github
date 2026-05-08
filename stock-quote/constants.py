from typing import Final
COMPAN_LEN: Final[int] = 28
NUM_PORTFOLIO_SEC: Final[int] = 17

# class Constants:
#     COMPAN_LEN = 16

#     # Block attribute modification
#     def __setattr__(self, name, value):
#         raise TypeError("Cannot modify a constant")


# add new displaying data
###     0   1  2  3   4  5  6  7  8   9  10 11 12 13 14
tabw = [18, 9, 7, 1, 11, 8, 9, 9, 9, 11, 14, 9, 9, 8, 8]

headers = ['Loading Time','Account','Symbl',' ','TotalGL','Change','Price','TodayGL','PctAcct','Shares','CurrentValue','52WK Lo','52WK Hi','PRC-Lo','Hi-PRC']
# tabw = [len(headers)]
# for idx in range(0, len(headers)):
#     tabw[idx] = len(headers[idx]) + 2
# tabw[0] = len(headers[0]) + 8
# print(tabw)