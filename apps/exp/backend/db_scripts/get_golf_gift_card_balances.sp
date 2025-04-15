DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_golf_gift_card_balances`(IN paymId INT, IN cardNum INT)
BEGIN

SELECT g.id AS id,date_format(g.spend_datetime,'%Y-%m-%d %H:%i') AS spendDate,g.spend_id AS spendId,
g.pay_method_id AS paymId,s.totalpaid AS cost,g.balance AS balance,g.card_num AS card_num 

FROM gift_card_balances g 
JOIN spends s on g.spend_id = s.id
WHERE g.pay_method_id = paymId AND g.card_num = cardNum
ORDER BY g.spend_datetime DESC;

/* ===============================
DELIMITER $$
CREATE DEFINER=swang@localhost PROCEDURE get_golf_gift_card_balances(IN paymId INT, IN cardNum INT)
BEGIN

SELECT g.id AS id,date_format(g.spend_datetime,'%Y-%m-%d %H:%i') AS spendDate,g.spend_id AS spendId,
g.pay_method_id AS paymId,s.totalpaid AS cost,g.balance AS balance,g.card_num AS card_num 

FROM gift_card_balances g 
JOIN spends s on g.spend_id = s.id
WHERE g.pay_method_id = paymId AND g.card_num = cardNum
ORDER BY g.spend_datetime DESC;

END$$
DELIMITER ;
*/