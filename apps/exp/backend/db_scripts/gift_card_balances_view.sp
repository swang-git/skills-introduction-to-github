select `g`.`id` AS `id`,date_format(`g`.`spend_datetime`,'%Y-%m-%d %H:%i') AS `spendDate`,`g`.`spend_id` AS `spendId`,
`g`.`pay_method_id` AS `paymId`,`s`.`totalpaid` AS `cost`,`g`.`balance` AS `balance`,`g`.`card_num` AS `card_num` 

from gift_card_balances g 
-- join spends s on g.spend_datetime = s.purchasedon 
join spends s on g.spend_id = s.id
order by g.spend_datetime desc
