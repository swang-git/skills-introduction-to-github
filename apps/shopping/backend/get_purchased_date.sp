CREATE DEFINER=`swang`@`%` PROCEDURE `get_purchased_date`(name VARCHAR(16))
BEGIN
select p.date from purchases p 
join shopping_items s on p.item_id = s.id 
where p.payee_id > 0 and s.name like concat('%', name, '%') COLLATE utf8_unicode_ci
order by p.date desc 
limit 1; 
END
