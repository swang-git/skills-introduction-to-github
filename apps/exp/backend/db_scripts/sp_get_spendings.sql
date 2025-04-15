BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId,
	su.name as subc, sp.payee_id as payeId, payeeName(sp.cat_id, sp.subcat_id, sp.payee_id) as paye, sp.paymethod_id as paymId,
	pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip', true as 'hideIt', sp.quantity as quan, sp.miles as 'mile', sp.notes as note,
    sp.link, gc.balance as 'gcardVal', gc.card_num as 'gcardNum', gc.id as 'gcardId', prev_balance(sp.purchasedon, sp.totalpaid, sp.paymethod_id) as prevbal,
    sp.post_date, sp.created_at, sp.updated_at
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
join pay_methods pm on sp.paymethod_id = pm.id
left join gift_card_balances gc on gc.spend_id = sp.id
where
	CASE WHEN spendId > 0 THEN sp.id = spendId ELSE TRUE END AND
	sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pm.status = 'A' and sp.user_id = userId
order by sp.purchasedon desc;
END

======================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_spendings`(IN `userId` INT, spendId INT)
BEGIN
select sp.id, sp.user_id, DATE_FORMAT(sp.purchasedon, '%Y-%m-%d %H:%i') as `date`, sp.cat_id as catsId, ca.name as cats, sp.subcat_id as subcId,
	su.name as subc, sp.payee_id as payeId, payeeName(sp.cat_id, sp.subcat_id, sp.payee_id, sp.purchasedon) as paye, sp.paymethod_id as paymId,
	pm.name as 'paym', sp.totalpaid as 'cost', sp.unitprice as 'unip', true as 'hideIt', sp.quantity as quan, sp.miles as 'mile', sp.notes as note,
    sp.link, gc.balance as 'gcardVal', gc.card_num as 'gcardNum', gc.id as 'gcardId', prev_balance(sp.purchasedon, sp.totalpaid, sp.paymethod_id) as prevbal,
    sp.post_date, sp.created_at, sp.updated_at
from spends sp
join categories ca on sp.cat_id = ca.id
join subcategories su on sp.subcat_id = su.id
join pay_methods pm on sp.paymethod_id = pm.id
left join gift_card_balances gc on gc.spend_id = sp.id
where
	CASE WHEN spendId > 0 THEN sp.id = spendId ELSE TRUE END AND
	sp.status = 'A' and ca.status = 'A' and su.status = 'A' and pm.status = 'A' and sp.user_id = userId
order by sp.purchasedon desc;
END
