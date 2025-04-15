DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_golf_play_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%Y-%m-%d') as due_in, 
    date_format(purchasedon, '%H:%i') as 'recursive',
    payeeName(2, s.subcat_id, s.payee_id) as tag, CONCAT('Golf Tee Time at ', date_format(purchasedon, '%H:%i')) as message, notes as details, 
    datediff(purchasedon, now()) as dueInDays
from spends s
where cat_id = 2 and (s.subcat_id = 4 or s.subcat_id = 190 or s.subcat_id = 273 or subcat_id = 302 or subcat_id = 303 or subcat_id = 304) 
	and s.status = 'A' and datediff(purchasedon, now()) between 0 and 62 -- get games in next 62 days (2 months)
order by purchasedon desc;
END$$
DELIMITER ;
=======================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_golf_play_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%Y-%m-%d') as due_in,
    date_format(purchasedon, '%H:%i') as 'recursive',
    payeeName(2, 4, payee_id) as tag, 'Golf' as message, notes as details,
	datediff(purchasedon, now()) as dueInDays
from spends s
where cat_id = 2 and s.subcat_id = 4 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 62 -- get 2 months from now
order by purchasedon desc;
END

=========================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_golf_play_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%Y-%m-%d') as due_in,
    date_format(purchasedon, '%H:%i') as 'recursive', payeeName(2, 4, payee_id, purchasedon) as type, null as message, notes as details,
    datediff(purchasedon, now()) as dueInDays
from spends s
where cat_id = 2 and s.subcat_id = 4 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 31
order by purchasedon desc;
END
========================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_golf_play_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%H:%i') as due_in,  'Golf' as 'recursive', payeeName(2, 4, payee_id, purchasedon) as type, null as message, notes as details,
datediff(purchasedon, now()) as dueInDays
from spends s
where cat_id = 2 and s.subcat_id = 4 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 31
order by purchasedon desc;
END
========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_golf_play_from_spends_for_reminders`()
BEGIN
select s.id, s.user_id, date_format(purchasedon, '%Y-%m-%d') as due_date, date_format(purchasedon, '%H:%i') as due_in,  'Golf' as 'recursive', payeeName(2, 4, payee_id, purchasedon) as type, null as message, notes as details,
datediff(purchasedon, now()) as dueInDays
from spends s
where cat_id = 2 and s.subcat_id = 4 and s.status = 'A' and datediff(purchasedon, now()) between 0 and 31
order by purchasedon desc;
END

