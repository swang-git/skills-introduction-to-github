DROP PROCEDURE IF EXISTS get_db_matched_spending;
DELIMITER $$;
CREATE DEFINER=swang@localhost PROCEDURE get_db_matched_spending(IN userId INT, IN cost DECIMAL(10,2), IN openDate DATE, IN closeDate DATE, IN postDate DATE)

BEGIN
SET @tolorateDate:=1;
SET @odate:=DATE_ADD(openDate,  INTERVAL -@tolorateDate DAY);
SET @cdate:=DATE_ADD(closeDate, INTERVAL  @tolorateDate DAY);

SELECT s.id AS id,
  DATE_FORMAT(s.purchasedon,'%Y-%m-%d %H:%i') AS purchasedate,
  s.post_date AS postdate,
  IF ((s.cat_id = 2 AND s.subcat_id = 4), c.name, p.name) AS payee,
  s.totalpaid AS cost,
  s.paymethod_id,
  DATEDIFF(postDate, s.purchasedon) AS ppdis
FROM spends s 
LEFT JOIN golf.courses c ON s.payee_id = c.id AND s.cat_id = 2 AND  s.subcat_id = 4 AND c.status = 'A'
LEFT JOIN payees p ON s.payee_id = p.id 
WHERE s.status = 'A'
  AND p.status = 'A' 
  AND s.user_id = userId
  AND s.totalpaid = cost
  AND s.paymethod_id in (10, 18)
  AND s.purchasedon between @odate AND @cdate
  AND DATEDIFF(postDate, s.purchasedon) >= 0
  ORDER BY ppdis
  LIMIT 1;
END $$;

========================================
DELIMITER $$
CREATE DEFINER=swang@localhost PROCEDURE get_db_matched_spending(IN userId INT, IN cost DECIMAL(10,2), IN openDate DATE, IN closeDate DATE, IN postDate DATE)
BEGIN

SELECT s.id AS id,
  DATE_FORMAT(s.purchasedon,'%Y-%m-%d %H:%i') AS purchasedate,
  s.post_date AS postdate,
  IF ((s.cat_id = 2 AND s.subcat_id = 4), c.name, p.name) AS payee,
  s.totalpaid AS cost,
  s.paymethod_id
FROM spends s 
LEFT JOIN golf.courses c ON s.payee_id = c.id AND s.cat_id = 2 AND  s.subcat_id = 4 AND c.status = 'A'
LEFT JOIN payees p ON s.payee_id = p.id -- AND (s.cat_id != 2 OR s.subcat_id != 4)
WHERE s.status = 'A'
  AND p.status = 'A' 
  AND s.user_id = userId
  AND s.totalpaid = cost
  AND s.paymethod_id in (10, 18)
  AND ((openDate <= DATE_FORMAT(purchasedon, '%Y-%m-%d') AND DATE_FORMAT(purchasedon, '%Y-%m-%d') <= closeDate) OR s.post_date = postDate)
  -- AND (DATE_FORMAT(s.purchasedon,'%Y-%m-%d') BETWEEN tranDate AND postDate OR s.post_date = postDate)
  -- AND s.purchasedon BETWEEN tranDate AND postDate OR s.post_date = postDate
ORDER BY s.totalpaid;

END$$
DELIMITER ;

/* ===============================
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_credit_card_spendings`(IN `userId` INT, IN `start_day` DATE, IN `end_day` DATE, IN `due_day` DATE)
BEGIN

SELECT sp.id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as date, ca.name as cats, sc.name as subc, sp.totalpaid as cost,
	
    IF(sp.reconciled_at IS NULL, 0, 1) as isReconed

FROM spends sp
JOIN categories ca on ca.id = sp.cat_id
JOIN subcategories sc on sc.id = sp.subcat_id
WHERE ((purchasedon BETWEEN start_day AND end_day AND post_date IS NULL) 
      OR (post_date BETWEEN start_day AND end_day) OR (sc.name = 'Refund' and sp.post_date = due_day))
	AND sp.user_id = userId 
  AND ca.user_id = userId 
  AND sc.user_id = userId
  AND (paymethod_id = 10 OR paymethod_id = 18)
  AND sp.status = 'A' 
  AND ca.status = 'A' 
  AND sc.status = 'A'
	AND (sp.reconciled_at IS NULL
	    OR DATE_FORMAT(sp.reconciled_at,'%Y-%m-%d') = DATE_FORMAT(due_day,'%Y-%m-%d'))

ORDER BY totalpaid;
END$$
DELIMITER ;
*/
  
