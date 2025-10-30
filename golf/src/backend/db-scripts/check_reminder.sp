DELIMITER $$
CREATE DEFINER=`swang`@`%localhost` PROCEDURE `check_reminder`(IN `date` DATE)
BEGIN

DECLARE table_exists INT DEFAULT 0;

SELECT COUNT(table_name) INTO table_exists FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'prod' AND TABLE_NAME = 'reminders';

IF table_exists THEN
  SELECT due_date, tag, message FROM devx.reminders WHERE DUE_DATE = date AND status = 'A';
ELSE
  select "No table reminders or No database prod";
DELIMITER $$

==========================
CREATE DEFINER=`swang`@`%localhost` PROCEDURE `check_reminder`(IN `date` DATE)
select due_date, tag, message from prod.reminders 
where due_date = date and status = 'A'$$
DELIMITER ;
