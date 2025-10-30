CREATE DEFINER=`swang`@`%` PROCEDURE `get_shopping_items`(dt DATE)
BEGIN

SELECT IF(p.date = dt, p.id, NULL) AS id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, 
IF(p.date = dt, 'A', 'D') as status
from shopping_items t 
left join (select * from shopping_purchases x where x.date = dt and x.status = 'A' and x.costs > 0 ) p on t.id = p.item_id
where t.status = 'A' -- and p.status = 'A' and p.costs > 0
order by t.class_id;

END

====================================

CREATE DEFINER=`swang`@`%` PROCEDURE `get_shopping_items`(dt DATE)
BEGIN
-- SELECT date_format(now(), '%Y-%m-%d') INTO @today; 
/* IF dt is NULL THEN
	SELECT MAX(date) INTO @date FROM purchases;
ELSE
	SET @date = dt;
END IF;
*/
-- SELECT @date;

-- select p.id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, IF(p.status = 'A' AND @date = @today, 'Yes', 'No') as checked
SELECT IF(p.date = dt, p.id, NULL) AS id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, 
IF(p.date = dt, 'A', 'D') as status
from shopping_items t 
left join (select * from purchases x where x.date = dt /* and x.status = 'A' */ ) p on t.id = p.item_id
where t.status = 'A'
order by t.class_id;

END


================================================

CREATE DEFINER=`swang`@`%` PROCEDURE `get_shopping_items`(dt DATE)
BEGIN

SELECT date_format(now(), '%Y-%m-%d') INTO @today; 
IF dt is NULL THEN
	SELECT MAX(date) INTO @date FROM purchases;
ELSE
	SET @date = dt;
END IF;
-- SELECT @date;

-- select p.id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, IF(p.status = 'A' AND @date = @today, 'Yes', 'No') as checked
SELECT IF(p.date = @today, p.id, NULL) AS id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, 
IF(p.date = @today, 'A', 'D') as status
from shopping_items t 
left join (select * from purchases x where x.date = @date /* and x.status = 'A' */ ) p on t.id = p.item_id
where t.status = 'A'
order by t.class_id;

END