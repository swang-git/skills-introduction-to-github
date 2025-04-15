-- create VIEW GCB_view AS
-- create VIEW GiftCardBalanceView AS
CREATE VIEW g_c_b_views AS
SELECT g.id, 
  g.spend_datetime AS 'spendDate',
  g.spend_id AS 'spendId',
  g.pay_method_id AS 'paymId',
  s.totalpaid AS 'cost',
  g.balance
  FROM gift_card_balances g
  JOIN spends s ON g.spend_datetime = s.purchasedon
  ORDER BY g.spend_datetime DESC



-- CREATE
-- ALGORITHM = UNDEFINED
-- DEFINER=`swang`@`localhost` 
-- SQL SECURITY DEFINER
-- VIEW `GCB_view`

--  AS select 1 AS `id`,1 AS `date`,1 AS `acct_num`,1 AS 
--  `acct_name`,1 AS `symbol`,1 AS `company`,1 AS `price`,1 