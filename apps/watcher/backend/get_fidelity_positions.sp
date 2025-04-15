SELECT fp.id AS id, fp.date AS date, fp.acct_num AS acct_num,
  fp.acct_name AS acct_name, fp.symbol AS symbol, fp.company AS company,
  fp.price AS price, fp.pchange AS pchange, fp.quantity AS quantity,
  fp.pct_of_acct AS pct_of_acct,
  (CASE WHEN (fp.cost_basis_per_share = '--') THEN sm.basis_price ELSE fp.cost_basis_per_share END) AS cost_basis_per_share,
  (CASE WHEN (fp.total_gl = '--') THEN (fp.current_val - (sm.basis_price * fp.quantity)) ELSE fp.total_gl END) AS total_gl,
  (CASE WHEN (fp.total_gl_p = '--') THEN (((fp.current_val - (sm.basis_price * fp.quantity)) / (sm.basis_price * fp.quantity)) * 100) ELSE fp.total_gl_p END) AS total_gl_p,
  (CASE WHEN (fp.cost_basis = '--') THEN (sm.basis_price * fp.quantity) ELSE fp.cost_basis END) AS cost_basis,
  fp.today_gl AS today_gl,fp.today_gl_p AS today_gl_p,fp.current_val AS current_val,
  ((TO_DAYS(fp.date) - TO_DAYS(sm.basis_date)) / 365) AS holding_time 
FROM fidelity_positions fp 
LEFT JOIN security_metas sm on sm.symbol = fp.symbol 
WHERE fp.date = date
ORDER by fp.today_gl DESC
