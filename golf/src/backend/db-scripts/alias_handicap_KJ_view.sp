select `p`.`id` AS `player_id`,`mp`.`alias` AS `alias`,
`ph`.`handicap` AS `handicap`,`p`.`gender` AS `gender`,
0 AS `grp`,NULL AS `team`,concat(`p`.`lastname`,', ',`p`.`firstname`) AS `name`,
`ph`.`last_handicap_date` AS `last_handicap_date` 
from ((`golf_dev`.`players` `p` 
join `golf_dev`.`match_players` `mp` on((`mp`.`player_id` = `p`.`id`)))
join `golf_dev`.`match_player_handicaps` `ph` on((`ph`.`match_player_id` = `mp`.`id`))) 
where ((`p`.`status` = 'A') and (`mp`.`status` = 'A') and (`ph`.`status` = 'A'))


CREATE VIEW alias_handicap_KJ_view AS
SELECT
p.id as player_id,
mp.alias,
ph.handicap,
CONCAT(p.lastname, ', ', p.firstname) AS name,
ph.last_handicap_date
FROM players p
JOIN match_players mp ON mp.player_id = p.id
JOIN match_player_handicaps ph ON ph.match_player_id = mp.id
WHERE p.status = 'A' AND mp.status = 'A' AND ph.status = 'A'
