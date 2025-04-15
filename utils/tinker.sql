BankStatementActivity::where([['year', 2023], ['month', 2], ['account_num', '226-227936']])->delete();

BankStatementHolding::where([['year', 2023], ['month', 2], ['account_num', 'X85-275143']])->delete();

