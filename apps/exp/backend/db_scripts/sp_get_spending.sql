CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_spending`(IN `userId` INT, IN `spendId` INT)
BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId, su.name as subc,
    sp.payee_id as payeId, payeeName(sp.cat_id, sp.subcat_id, sp.payee_id, sp.purchasedon) as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.quantity as quan, sp.miles as 'mile', sp.notes as note, sp.link, gc.balance as 'gcardVal', gc.card_num as 'gcardNum', gc.id as 'gcardId'
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
join pay_methods pm on sp.paymethod_id = pm.id
LEFT JOIN gift_card_balances gc on sp.id = gc.spend_id

where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pm.status = 'A' and sp.user_id = userId and sp.id = spendId;
END

=================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_spending`(userId INT, spendId INT)
BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId, su.name as subc,
	-- sp.payee_id as payeId, pa.name as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.payee_id as payeId, payeeName(sp.cat_id, sp.subcat_id, sp.payee_id, sp.purchasedon) as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.quantity as quan, sp.miles as 'mile', sp.notes as note, sp.link, gc.balance as 'gcardVal', gc.card_num as 'gcardNum', gc.id as 'gcardId'
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
-- join payees pa on sp.payee_id = pa.id
join pay_methods pm on sp.paymethod_id = pm.id
LEFT JOIN gift_cards gc on sp.id = gc.spend_id

-- IF Id > 0 TEHN
where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pm.status = 'A' and sp.user_id = userId and sp.id = spendId;
--	and (case when spendId > 0 then sp.id = spendId end)
-- ELSE where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pa.status = 'A' and pm.status = 'A' and sp.user_id = userId;
-- END IF
-- order by sp.purchasedon desc;
END

=======================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_spending`(userId INT, spendId INT)
BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId, su.name as subc,
	-- sp.payee_id as payeId, pa.name as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.payee_id as payeId, payeeName(sp.cat_id, sp.subcat_id, sp.payee_id, sp.purchasedon) as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.quantity as quan, sp.miles as 'mile', sp.notes as note, sp.link
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
-- join payees pa on sp.payee_id = pa.id
join pay_methods pm on sp.paymethod_id = pm.id

-- IF Id > 0 TEHN
where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pm.status = 'A' and sp.user_id = userId and sp.id = spendId;
--	and (case when spendId > 0 then sp.id = spendId end)
-- ELSE where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pa.status = 'A' and pm.status = 'A' and sp.user_id = userId;
-- END IF
-- order by sp.purchasedon desc;
END








===========================================
use dev;
DROP PROCEDURE IF EXISTS get_spending;
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_spending`(userId INT, spendId INT)
BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId, su.name as subc,
        sp.payee_id as payeId, pa.name as paye, sp.paymethod_id as paymId, pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip',
    sp.quantity as quan, sp.miles as 'mile', sp.notes as note, sp.link
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
join payees pa on sp.payee_id = pa.id
join pay_methods pm on sp.paymethod_id = pm.id

-- IF Id > 0 TEHN
where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pa.status = 'A' and pm.status = 'A' and sp.user_id = userId and sp.id = spendId;
--	and (case when spendId > 0 then sp.id = spendId end)
-- ELSE where sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pa.status = 'A' and pm.status = 'A' and sp.user_id = userId;
-- END IF
-- order by sp.purchasedon desc;
END$$
DELIMITER ;

--     CALL get_spending(1, 1227);
-- select * from spends where id > 1216;
