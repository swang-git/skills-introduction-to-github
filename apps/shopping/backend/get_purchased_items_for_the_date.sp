CREATE DEFINER=`swang`@`%` PROCEDURE `get_purchased_items_for_the_date`(dt DATE)
BEGIN

SELECT IF(p.date = dt, p.id, NULL) AS id, p.date, t.id as itemId, t.name, p.price, p.units, p.costs, t.class_id, 
IF(p.date = dt, 'A', 'D') as status
from shopping_items t 
left join (select * from shopping_purchases x where x.date = dt and x.status = 'A' and x.costs > 0) p on t.id = p.item_id
where t.status = 'A' -- and p.status = 'A' and p.costs > 0
order by t.class_id;

END
