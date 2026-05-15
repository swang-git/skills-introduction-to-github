from typing import Final
COMPAN_LEN: Final[int] = 28
NUM_PORTFOLIO_SEC: Final[int] = 17
spx = ""

# class Constants:
#     COMPAN_LEN = 16

#     # Block attribute modification
#     def __setattr__(self, name, value):
#         raise TypeError("Cannot modify a constant")


# add new displaying data
###     0   1  2  3   4  5  6  7  8   9  10 11 12 13 14
# tabw = [18, 9, 7, 1, 11, 8, 9, 9, 9, 11, 14, 9, 9, 8, 8]

# headers = ['Loading Time','Account','Symbl','x','TotalGL','Change','Price','TodayGL','PctAcct','Shares','CurrentValue','52WK Lo','52WK Hi','PRC-Lo','Hi-PRC']
headers = ['Loading Time','Account','TotalG/L','TotGL%','Symbl','x','Change','Price','TodayG/L','PctAcct','Shares','CurrentValue','52WK Lo','52WK Hi','PRC-Lo','Hi-PRC']
tabw = [None] * len(headers)
for idx in range(0, len(headers)):
    if headers[idx] == 'x': tabw[idx] = 1
    elif headers[idx] == 'TotalG/L': tabw[idx] = len(headers[idx]) + 3
    elif headers[idx] == 'Price': tabw[idx] = len(headers[idx]) + 4
    elif headers[idx] == 'Shares': tabw[idx] = len(headers[idx]) + 5
    elif headers[idx] == 'Loading Time': tabw[idx] = len(headers[idx]) + 6
    else: tabw[idx]= len(headers[idx]) + 2
