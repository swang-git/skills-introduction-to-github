DROP PROCEDURE `get_credit_card_spendings`;
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(IN `userId` INT, IN `start_day` DATE, IN `end_day` DATE, IN `due_day` DATE)
NOT DETERMINISTIC 
CONTAINS SQL 
SQL SECURITY DEFINER 
BEGIN SELECT sp.id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost, IF(sp.reconciled_at IS NULL, 0, 1) as isReconed 
FROM spends sp 
JOIN categories ca on ca.id = sp.cat_id 
JOIN subcategories sc on sc.id = sp.subcat_id 
WHERE ((purchasedon BETWEEN start_day 
AND end_day 
AND post_date IS NULL) OR (post_date BETWEEN start_day AND end_day) OR (sc.name = 'Refund' and sp.post_date = due_day)) 
AND sp.user_id = userId 
AND ca.user_id = userId 
AND sc.user_id = userId 
AND paymethod_id = 10 
AND sp.status = 'A' 
AND ca.status = 'A' 
AND sc.status = 'A' 
AND (sp.reconciled_at IS NULL OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d')) 
ORDER BY totalpaid; 
END 
================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(userId INT, start_day DATE, end_day DATE, due_day DATE)
BEGIN

SELECT sp.id, sp.purchasedon as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost,
	-- CASE WHEN sc.name = 'Refund' THEN -sp.totalpaid ELSE sp.totalpaid END as cost, 
    IF(sp.reconciled_at IS NULL, 0, 1) as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day AND post_date IS NULL) OR (post_date BETWEEN start_day AND end_day) OR (sc.name = 'Refund' and sp.post_date = due_day))
	AND sp.user_id = userId AND ca.user_id = userId AND sc.user_id = userId
    AND paymethod_id = 10 
    AND sp.status = 'A' AND ca.status = 'A' AND sc.status = 'A'
    -- AND sp.totalpaid > 0
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))
ORDER BY purchasedon;
END

===========================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(userId INT, start_day DATE, end_day DATE, due_day DATE)
BEGIN

SELECT sp.id, sp.purchasedon as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost,
	-- CASE WHEN sc.name = 'Refund' THEN -sp.totalpaid ELSE sp.totalpaid END as cost, 
    IF(sp.reconciled_at IS NULL, 0, 1) as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day) OR (post_date BETWEEN start_day AND end_day) OR (sc.name = 'Refund' and sp.post_date = due_day))
	AND sp.user_id = userId AND ca.user_id = userId AND sc.user_id = userId
    AND paymethod_id = 10 
    AND sp.status = 'A' AND ca.status = 'A' AND sc.status = 'A'
    -- AND sp.totalpaid > 0
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))
ORDER BY purchasedon;
END

============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(userId INT, start_day DATE, end_day DATE, due_day DATE)
BEGIN

SELECT sp.id, sp.purchasedon as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost,
	-- CASE WHEN sc.name = 'Refund' THEN -sp.totalpaid ELSE sp.totalpaid END as cost, 
    IF(sp.reconciled_at IS NULL, 'no', 'yes') as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day) OR (post_date BETWEEN start_day AND end_day) OR (sc.name = 'Refund' and sp.post_date = due_day))
	AND sp.user_id = userId AND ca.user_id = userId AND sc.user_id = userId
    AND paymethod_id = 10 
    AND sp.status = 'A' AND ca.status = 'A' AND sc.status = 'A'
    -- AND sp.totalpaid > 0
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))
ORDER BY purchasedon;
END
=============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(userId INT, start_day DATE, end_day DATE, due_day DATE)
BEGIN

SELECT sp.id, sp.purchasedon as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost,
	-- CASE WHEN sc.name = 'Refund' THEN -sp.totalpaid ELSE sp.totalpaid END as cost, 
    IF(sp.reconciled_at IS NULL, 'no', 'yes') as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day) OR (post_date BETWEEN start_day AND end_day))
	AND sp.user_id = userId AND ca.user_id = userId AND sc.user_id = userId
    AND paymethod_id = 10 
    AND sp.status = 'A' AND ca.status = 'A' AND sc.status = 'A'
    -- AND sp.totalpaid > 0
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))
ORDER BY purchasedon;
END

==============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(userId INT, start_day DATE, end_day DATE, due_day DATE)
BEGIN

SELECT sp.id, sp.purchasedon as date, ca.name as cats, sc.name as subc,
	CASE WHEN sc.name = 'Refund' THEN -sp.totalpaid ELSE sp.totalpaid END as cost, IF(sp.reconciled_at IS NULL, 'no', 'yes') as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day) OR (post_date BETWEEN start_day AND end_day))
	AND sp.user_id = userId AND ca.user_id = userId AND sc.user_id = userId
    AND paymethod_id = 10
    AND sp.status = 'A' AND ca.status = 'A' AND sc.status = 'A'
    AND sp.totalpaid > 0
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))
ORDER BY purchasedon;
END
