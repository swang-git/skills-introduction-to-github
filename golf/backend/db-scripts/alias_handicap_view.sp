create view alias_handicap_view AS
SELECT p.id AS player_id, game_id,
    a.alias AS alias,
    handicap_last_10_handi_diff(p.id)  AS handicap,
    p.gender AS gender,
    CONCAT(p.lastname, ', ', p.firstname) AS name
FROM players p
    JOIN player_aliases a ON a.player_id = p.id
WHERE p.status = 'A' AND a.status = 'A'
