
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_positions`(IN `date` DATE)
BEGIN

DECLARE loadTime DATETIME;
SELECT max(DATE_FORMAT(load_time, '%Y-%m-%d %H:%i')) 
INTO loadTIme
FROM stock_quotes
ORDER BY load_time desc;

SELECT * FROM (
SELECT fp.id AS id, fp.date AS date, fp.acct_num AS acct_num,
  fp.acct_name AS acct_name, fp.symbol AS symbol, fp.company AS company,
  fp.price AS price, fp.pchange AS pchange, fp.quantity AS quantity,
  fp.pct_of_acct AS pct_of_acct,
  (CASE WHEN (fp.cost_basis_per_share = '--') THEN sm.basis_price ELSE fp.cost_basis_per_share END) AS cost_basis_per_share,
  (CASE WHEN (fp.total_gl = '--') THEN (fp.current_val - (sm.basis_price * fp.quantity)) ELSE fp.total_gl END) AS total_gl,
  (CASE WHEN (fp.total_gl_p = '--') THEN (((fp.current_val - (sm.basis_price * fp.quantity)) / (sm.basis_price * fp.quantity)) * 100) ELSE fp.total_gl_p END) AS total_gl_p,
  (CASE WHEN (fp.cost_basis = '--') THEN (sm.basis_price * fp.quantity) ELSE fp.cost_basis END) AS cost_basis,
  fp.today_gl AS today_gl,fp.today_gl_p AS today_gl_p,fp.current_val AS current_val,
  ((TO_DAYS(fp.date) - TO_DAYS(sm.basis_date)) / 365) AS holding_time,
  null AS day_low,null AS day_high,null AS w52_low, null AS w52_high, 0 as odx
FROM fidelity_positions fp 
LEFT JOIN security_metas sm on sm.symbol = fp.symbol 
WHERE fp.date = date
) AS t1

UNION

SELECT * FROM(
SELECT s.id as id, DATE_FORMAT(s.load_time, '%Y-%m-%d') as date, '985-10278' as acct_num,
  'Chase Brockerage' as acct_name, s.symbol, s.symbol as company,
  s.price, s.price_change as pchange, p.quantity, 22 as pct_of_acct, 
  p.total_cost / p.quantity as cost_basis_per_cost,
  p.quantity * s.price - p.total_cost as total_gl,
  (p.quantity * s.price - p.total_cost) / p.total_cost * 100 as total_gl_p,
  p.total_cost as cost_basis,
  s.price_change * p.quantity as today_gl,
  s.price_change / s.price * 100 as today_gl_p,
  s.price * p.quantity as current_val,
  ((TO_DAYS(date) - TO_DAYS('2000-1-1')) / 365) AS holding_time,
  s.day_low, s.day_high, s.low_52_week, s.high_52_week, 1 as odx
FROM stock_quotes s

JOIN security_metas p on s.symbol = p.symbol
WHERE s.symbol in ('T', 'DELL', 'CSCO', 'WBD', 'MSFT', 'CHTR')
	-- DATE_FORMAT(s.load_time, '%Y-%m-%d %H:%i:00') = loadTime 
	AND s.status = 'A' and p.status = 'A'
    order by load_time DESC
    limit 6
) AS t2
ORDER by odx, today_gl DESC;
END$$
DELIMITER ;

===================

DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_positions`(IN `date` DATE)
BEGIN

DECLARE loadTime DATETIME;
SELECT max(DATE_FORMAT(load_time, '%Y-%m-%d %H:%i')) 
INTO loadTIme
FROM stock_quotes
ORDER BY load_time desc;

SELECT fp.id AS id, fp.date AS date, fp.acct_num AS acct_num,
  fp.acct_name AS acct_name, fp.symbol AS symbol, fp.company AS company,
  fp.price AS price, fp.pchange AS pchange, fp.quantity AS quantity,
  fp.pct_of_acct AS pct_of_acct,
  (CASE WHEN (fp.cost_basis_per_share = '--') THEN sm.basis_price ELSE fp.cost_basis_per_share END) AS cost_basis_per_share,
  (CASE WHEN (fp.total_gl = '--') THEN (fp.current_val - (sm.basis_price * fp.quantity)) ELSE fp.total_gl END) AS total_gl,
  (CASE WHEN (fp.total_gl_p = '--') THEN (((fp.current_val - (sm.basis_price * fp.quantity)) / (sm.basis_price * fp.quantity)) * 100) ELSE fp.total_gl_p END) AS total_gl_p,
  (CASE WHEN (fp.cost_basis = '--') THEN (sm.basis_price * fp.quantity) ELSE fp.cost_basis END) AS cost_basis,
  fp.today_gl AS today_gl,fp.today_gl_p AS today_gl_p,fp.current_val AS current_val,
  ((TO_DAYS(fp.date) - TO_DAYS(sm.basis_date)) / 365) AS holding_time,
  null AS day_low,null AS day_high,null AS w52_low, null AS w52_high, 0 as odx
FROM fidelity_positions fp 
LEFT JOIN security_metas sm on sm.symbol = fp.symbol 
WHERE fp.date = date

UNION

SELECT s.id as id, DATE_FORMAT(s.load_time, '%Y-%m-%d') as date, '985-10278' as acct_num,
  'Chase Brockerage' as acct_name, s.symbol, s.symbol as company,
  s.price, s.price_change as pchange, p.quantity, 22 as pct_of_acct, 
  p.total_cost / p.quantity as cost_basis_per_cost,
  p.quantity * s.price - p.total_cost as total_gl,
  (p.quantity * s.price - p.total_cost) / p.total_cost * 100 as total_gl_p,
  p.total_cost as cost_basis,
  s.price_change * p.quantity as today_gl,
  s.price_change / s.price * 100 as today_gl_p,
  s.price * p.quantity as current_val,
  ((TO_DAYS(date) - TO_DAYS('2000-1-1')) / 365) AS holding_time,
  s.day_low, s.day_high, s.low_52_week, s.high_52_week, 1 as odx
FROM stock_quotes s

JOIN security_metas p on s.symbol = p.symbol
WHERE DATE_FORMAT(s.load_time, '%Y-%m-%d %H:%i:00') = loadTime AND s.status = 'A' and p.status = 'A'
-- WHERE DATE_FORMAT(s.load_time, '%Y-%m-%d %H:%i') = '2024-12-03 17:50' AND s.status = 'A' and p.status = 'A'

ORDER by odx, today_gl DESC;
-- select loadTime;

END$$
DELIMITER ;
