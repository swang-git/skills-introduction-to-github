from Utils import cleanMoney, mval, isFloat
from functools import partial
d2o=partial(type, "d2o", ())
DEBUG=None
def yourPortfolioValue(lines):
  for i, line in enumerate(lines):
    if DEBUG: print(' -- ThisPeriodAndYear-to-Dat', i, line)
    if line == 'Your Portfolio Value:':
      val = next(lines)
      next(lines)
      chg = next(lines)
      portval = {'value': mval(val), 'change': mval(chg)}
      return portval
      # return d2o(portval)

def ThisPeriodAndYearToDate(lines):
  for i, line in enumerate(lines):
    if DEBUG: print(' -- ThisPeriodAndYearToDat', i, line)
    if line == 'Beginning Portfolio Value':
      bpval = next(lines)
      byval = next(lines)
      next(lines)
      adds = next(lines)
      yadds = next(lines)
      if not isFloat(mval(yadds)): yadds = adds; adds = '0'
      else: next(lines)
      subs = next(lines)
      ysubs = next(lines)
      next(lines)
      schg = next(lines)
      yschg = next(lines)
      if DEBUG: print('bpval[%s], byval[%s], adds[%s], yadds[%s]'%(bpval, byval, adds, yadds))
      stmtatv = {'value': mval(bpval), 'adds': mval(adds),  'subs': mval(subs),  'stmtchg': mval(schg) }
      yearatv = {'value': mval(byval), 'adds': mval(yadds), 'subs': mval(ysubs), 'ytodchg': mval(yschg)}
      return (stmtatv, yearatv)
      # print(stmtatv)

def EndingPortfolioValue(lines):
  for i, line in enumerate(lines):
    if DEBUG: print(' -- EndingPortfolioValue', i, line)
    if line == 'Ending Portfolio Value **':
      stmtval = next(lines)
      ytodval = next(lines)
      return {'stmt': mval(stmtval), 'ytod': mval(ytodval)}
      # return d2o({'stmt': mval(stmtval), 'ytod': mval(ytodval)})
