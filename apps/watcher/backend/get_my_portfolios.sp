BEGIN
SELECT myp.id AS id, myp.date AS date, myp.acct_num AS acct_num,
  myp.accountt_name AS acct_name, myp.symbol AS symbol, myp.company AS company,
  myp.price AS price, myp.price_change AS pchange, myp.quantity AS quantity,
  myp.pct_of_account AS pct_of_acct,
  myp.cost_per_share AS cost_basis_per_share,
  myp.total_gl AS total_gl,
  myp.total_gl_p AS total_gl_p,
  myp.cost_basis AS cost_basis,
  myp.current_val AS current_val,
  ((TO_DAYS(myp.date) - TO_DAYS(sm.basis_date)) / 365) AS holding_time,
  null AS day_low,null AS day_high,
  myp.low_52_week AS w52_low,
  myp.high_52_week AS w52_high,
  0 as odx
FROM my_portfolios myp
LEFT JOIN security_metas sm on sm.symbol = myp.symbol
WHERE DATE_FORMAT(myp.asof_time, '%Y-%m-%d') = date
ORDER by odx, today_gl DESC;
END
