USE `princeton_golf`;
CREATE  OR REPLACE VIEW `player_info` AS

select distinct 
    `pl`.`id` AS `id`,`pl`.`lastname` AS `lastname`,`pl`.`firstname` AS `firstname`,
    `pl`.`gender` AS `gender`,date_format(`tm`.`start_at`,'%Y') AS `year` 
from 
    ((`players` `pl` join `tplayers` `tp`) join `tournaments` `tm`) 
where 
    `pl`.`id` = `tp`.`player_id` 
and `tp`.`tournament_id` = `tm`.`id` 
and `tm`.`game_id` < 5 
and `pl`.`status` = 'A' 
and `tp`.`status` = 'A' 
and `tm`.`status` = 'A';
