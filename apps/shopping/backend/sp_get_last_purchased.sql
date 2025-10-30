CREATE DEFINER=`swang`@`%` PROCEDURE `get_last_purchased`(itemId INT)
BEGIN
-- DECLARE @today DATE;


SELECT p.date, p.item_id, p.price, p.units, p.costs, si.name
FROM shopping_purchases p
JOIN shopping_items si ON si.id = p.item_id
WHERE p.price > 0 AND p.units > 0 AND p.costs > 0
	AND p.item_id = itemId
    AND p.status = 'A' AND si.status = 'A'
    AND p.date < DATE_FORMAT(now(), '%Y-%m-%d')
ORDER BY p.date desc
LIMIT 1;
END
