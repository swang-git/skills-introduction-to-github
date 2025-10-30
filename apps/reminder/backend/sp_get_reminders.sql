CREATE DEFINER=`swang`@`%` PROCEDURE `get_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%H:%i') as due_in,  date_format(purchasedon, '%a') as 'recursive', p.name as type, null as message, notes as details, datediff(purchasedon, now())
from spends s, payees p
where s.payee_id = p.id and cat_id = 2 and s.subcat_id = 4 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 14 
order by purchasedon desc;
END

