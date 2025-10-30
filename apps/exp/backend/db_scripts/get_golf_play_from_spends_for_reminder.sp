CREATE DEFINER = `swang`@`localhost` PROCEDURE `get_golf_play_from_spends_for_reminders`() NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
BEGIN
    SELECT
        s.id, s.user_id, DATE_FORMAT(purchasedon, '%Y-%m-%d') AS due_date,
        DATE_FORMAT(purchasedon, '%Y-%m-%d') AS due_in,
        DATE_FORMAT(purchasedon, '%H:%i') AS 'recursive',
        payeeName(2, s.subcat_id, s.payee_id) AS tag,
        CONCAT(
            'Golf Tee Time at ',
            DATE_FORMAT(purchasedon, '%H:%i')
        ) AS message,
        notes AS details,
        DATEDIFF(purchasedon, NOW()) AS dueInDays,
        1 AS golf
    FROM
        spends s
    WHERE
        cat_id = 2 AND(
            s.subcat_id = 4 OR s.subcat_id = 190 OR s.subcat_id = 273 OR subcat_id = 302 OR subcat_id = 303 OR subcat_id = 304
        ) AND s.status = 'A' AND DATEDIFF(purchasedon, NOW()) BETWEEN 0 AND 62
    ORDER BY
        purchasedon
    DESC;
        END