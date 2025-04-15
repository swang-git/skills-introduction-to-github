CREATE VIEW match_player_handicaps_view AS
SELECT
    `mh`.`match_player_id` AS `match_player_id`,
    `mp`.`alias` AS `alias`,
    `pp`.`lastname` AS `lastname`,
    `pp`.`firstname` AS `firstname`,
    `mh`.`last_handicap_date` AS `last_handicap_date`,
    `mh`.`handicap` AS `handicap`
FROM
    (
        (
            `golf_dev`.`match_player_handicaps` `mh`
        JOIN `golf_dev`.`match_players` `mp`
        ON
            ((`mp`.`id` = `mh`.`match_player_id`))
        )
    JOIN `golf_dev`.`players` `pp`
    ON
        ((`pp`.`id` = `mp`.`player_id`))
    )
WHERE
    (
        (`mp`.`status` = 'A') AND(`mh`.`status` = 'A')
    )