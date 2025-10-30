BEGIN
SELECT p.id, p.firstname, p.lastname, m.fees, m.id as mid, m.status
FROM players p
LEFT JOIN memberships m ON p.id = m.player_id AND m.status = 'A' AND m.year = year
WHERE p.status = 'A' order by m.status desc, lastname, firstname;

END
