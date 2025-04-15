CREATE DEFINER=`swang`@`%` PROCEDURE `get_purchased_list`(purchasedDate DATE, payeeId INT)
BEGIN

SELECT p.id, p.date, t.id AS itemId, t.name, p.price, p.units, p.costs, payee_id,
IF(p.payee_id > 0, 'yes', 'no') AS payee_flag,
p.status, p.updated_at -- IF(p.status = 'A' AND @date = @today, 'Yes', 'No') as checked
from shopping_items t 
join purchases p on t.id = p.item_id
where t.status = 'A'
AND p.status = 'A'
-- AND p.date = DATE_FORMAT(checkoutTime, '%Y-%m-%d')
AND p.date = purchasedDate
AND (isNULL(p.payee_id) or p.payee_id = payeeId)

-- AND TIMESTAMPDIFF(MINUTE, p.updated_at, checkoutTime) >= 0  -- past 2 hours from checkout time
-- AND TIMESTAMPDIFF(MINUTE, p.updated_at, checkoutTime) <= 120  -- past 2 hours from checkout time
order by t.id;

END


-- CREATE PROCEDURE get_purchased_list(checkoutTime TIMESTAMP)
-- BEGIN

-- SELECT p.date, t.id, t.name, p.price, p.units, p.costs, p.status, p.updated_at -- IF(p.status = 'A' AND @date = @today, 'Yes', 'No') as checked
-- FROM shopping_items t 
-- JOIN purchases p ON t.id = p.item_id
-- where t.status = 'A'
-- AND p.status = 'A'
-- AND p.date = DATE_FORMAT(checkoutTime, '%Y-%m-%d')
-- -- AND TIMESTAMPDIFF(MINUTE, p.updated_at, checkoutTime) >= 0  -- past 2 hours from checkout time
-- -- AND TIMESTAMPDIFF(MINUTE, p.updated_at, checkoutTime) <= 120  -- past 2 hours from checkout time
-- ORDER BY t.id;

-- END
