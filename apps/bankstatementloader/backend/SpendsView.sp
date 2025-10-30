CREATE VIEW spends_view AS
SELECT s.id, DATE_FORMAT(s.purchasedon, '%Y-%m-%d %H:%i') AS purchasedate, s.post_date AS postdate, 
IF(s.cat_id = 2 AND s.subcat_id = 4, c.name, p.name) AS payee, s.totalpaid AS cost, paymethod_id
FROM spends s
LEFT JOIN payees p ON s.payee_id = p.id
LEFT JOIN golf.courses c ON s.payee_id = c.id
WHERE s.status = 'A' AND p.status = 'A'
ORDER BY s.purchasedon

