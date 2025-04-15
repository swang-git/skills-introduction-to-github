CREATE DEFINER=`swang`@`localhost` FUNCTION `prev_balance`(ptime DATETIME, cost float8, paymId INT) RETURNS float
    DETERMINISTIC
BEGIN
declare prevb float8;
declare newCardValue float8;
select balance into prevb from gift_card_balances where spend_datetime < ptime AND pay_method_id = paymId AND status = 'A' order by spend_datetime desc limit 1;

IF (prevb is NULL or prevb < cost) then
	select value into newCardValue 
    FROM gift_cards WHERE paym_id = paymId AND status = 'A'
    ORDER BY dtm desc LIMIT 1;
END IF;

IF (prevb < cost) then
	set prevb = prevb + newCardValue;
END IF;
RETURN prevb;
END

============================================
CREATE DEFINER=`swang`@`localhost` FUNCTION `prev_balance`(ptime DATETIME, cost float8, gcardNum INT, paymId INT) RETURNS float
    DETERMINISTIC
BEGIN
declare prevb float8;
select balance into prevb from gift_card_balances where spend_datetime < ptime AND card_num = gcardNum AND status = 'A' order by spend_datetime desc limit 1;

IF (prevb is NULL) then
	select value into prevb 
    FROM gift_cards WHERE card_num = gcardNum AND paym_id = paymId AND status = 'A'
    ORDER BY dtm desc LIMIT 1;
ELSEIF (prevb < cost) then
	set prevb = prevb + 600;
END IF;
RETURN prevb;
END