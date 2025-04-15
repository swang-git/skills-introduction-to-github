BEGIN

SELECT p.id, p.date, t.id as item_id, t.name, p.price, p.units, p.costs, t.class_id, t.class, p.payee_id, p.status
FROM shopping_items t
LEFT JOIN 
(SELECT id, date, name, price, units, costs, payee_id, item_id, status FROM shopping_purchases x 
    WHERE x.date=last_buy(item_id)) p ON t.id = p.item_id
WHERE t.status = 'A'
ORDER BY t.updated_at desc;

END

=============================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_shopping_list`()
BEGIN
SELECT p.id, p.date, t.id as item_id, t.name, p.price, p.units, p.costs, t.class_id, p.payee_id, p.status
-- IF(p.payee_id is NULL, 'S', 'A') as status
FROM shopping_items t
LEFT JOIN 
(SELECT id, date, name, price, units, costs, payee_id, item_id, status FROM shopping_purchases x 
    WHERE x.date=last_buy(item_id)) p ON t.id = p.item_id
WHERE t.status = 'A'
ORDER BY t.updated_at desc;
END
