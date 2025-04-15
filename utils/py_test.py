#!/usr/bin/python
import os, sys

import loading_ww_test

# import TEST_WX_loading
sys.exit(0)

from PyClasses import Security
saveToDB = False
funds = ['FFTWX', 'FSKAX', 'FXAIX']
stocks = ['CHTR', 'CSCO', 'DELL', 'MSFT', 'T']
sec = Security(saveToDB, funds, stocks)
sec.loadFund()
sec.loadStock()
