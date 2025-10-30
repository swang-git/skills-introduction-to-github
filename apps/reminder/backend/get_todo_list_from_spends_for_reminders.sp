BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%Y-%m-%d') as due_in, 
    date_format(purchasedon, '%H:%i') as 'recursive',
    payeeName(0, 0, payee_id) as type, 'Appointment' as message, notes as details, 
	datediff(purchasedon, now()) as dueInDays
from spends s
where totalpaid = 0.01 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 31
order by purchasedon desc;
END

=============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_todo_list_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%Y-%m-%d') as due_in, 
    date_format(purchasedon, '%H:%i') as 'recursive',
    payeeName(0, 0, payee_id) as type, 'To Do' as message, notes as details, 
	datediff(purchasedon, now()) as dueInDays
from spends s
where totalpaid = 0.01 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 31
order by purchasedon desc;
END
