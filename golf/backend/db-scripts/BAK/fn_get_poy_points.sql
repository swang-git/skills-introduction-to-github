USE `princeton_golf`;
DROP function IF EXISTS `get_poy_points`;

DELIMITER $$
USE `princeton_golf`$$
CREATE DEFINER=`pccadmin`@`%` FUNCTION `get_poy_points`(playerId INT, tournamentId INT) RETURNS decimal(3,1)
    DETERMINISTIC reads sql data
BEGIN
declare poy_points decimal(3,1);
declare tmnt_start_at datetime;
declare bound INT;
select tt.start_at from tournaments tt where tt.id = tournamentId into tmnt_start_at;
set bound = 17;

select sum(prank) from 
(select (case when bound - gross_rank > 0 then bound - gross_rank else 0 end ) as prank
from tplayers tp
join tournaments tt on tp.tournament_id = tt.id 
join players p on tp.player_id = p.id
where tt.game_id in (1, 2, 3, 4) and tp.player_id = playerId
and tt.year = date_format(tmnt_start_at, '%Y') 
and tt.start_at <= tmnt_start_at 
order by prank desc limit 3
) p into poy_points;

-- group by tp.player_id
-- order by prank desc

return (poy_points);

END$$

DELIMITER ;


-- CREATE DEFINER=`swang`@`localhost` FUNCTION `get_poy_points`(playerId INT, tournamentId INT) RETURNS decimal(3,1)
--     DETERMINISTIC
-- BEGIN
-- declare poy_points decimal(3,1);
-- declare tmnt_start_at datetime;
-- declare bound INT;
-- select tt.start_at from tournaments tt where tt.id = tournamentId into tmnt_start_at;
-- set bound = 17;

-- select sum(prank) from 
-- (select (case when bound - gross_rank > 0 then bound - gross_rank else 0 end ) as prank
-- from tplayers tp
-- join tournaments tt on tp.tournament_id = tt.id 
-- join players p on tp.player_id = p.id
-- where tt.game_id in (1, 2, 3, 4) and tp.player_id = playerId
-- and tt.year = date_format(tmnt_start_at, '%Y') 
-- and tt.start_at <= tmnt_start_at 
-- order by prank desc limit 3
-- ) p into poy_points;

-- -- group by tp.player_id
-- -- order by prank desc

-- return (poy_points);

-- END

