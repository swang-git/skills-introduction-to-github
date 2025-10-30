SELECT
    `p`.`id` AS `player_id`,
    `mp`.`alias` AS `alias`,
    `gd`.`games_played` AS `games_played`,
    `gd`.`net_win_loss` AS `net_win_loss`,
    `gd`.`avg_idx` AS `handicap`,
    `gd`.`all_games_played` AS `all_games_played`,
    `gd`.`avg_idx_all_games` AS `avg_idx_all_games`,
    `gd`.`avg_score` AS `avg_score`,
    `gd`.`pair_win_pct` AS `pair_win_pct`,
    `gd`.`team_win_pct` AS `team_win_pct`,
    `gd`.`gross_score` AS `gross_score`,
    `gd`.`idx_diff` AS `idx_diff`,
    `gd`.`pair` AS `pair`,
    `gd`.`pair_win_pnt` AS `pair_win_pnt`,
    `gd`.`team_win_pnt` AS `team_win_pnt`,
    `p`.`gender` AS `gender`,
    0 AS `grp`,
    NULL AS `team`,
    CONCAT(`p`.`lastname`, ', ', `p`.`firstname`) AS `name`,
    `gd`.`execl_date` AS `execl_date`
FROM
    (
        (
            `golf_dev`.`players` `p`
        JOIN `golf_dev`.`match_players` `mp`
        ON
            ((`mp`.`player_id` = `p`.`id`))
        )
    JOIN `golf_dev`.`kj_game_data` `gd`
    ON
        ((`gd`.`mp_id` = `mp`.`id`))
    )
WHERE
    (
        (`p`.`status` = 'A') AND(`mp`.`status` = 'A') AND(`gd`.`status` = 'A')
    )