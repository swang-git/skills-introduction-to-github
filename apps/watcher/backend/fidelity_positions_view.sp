CREATE VIEW fidelity_positions_view AS
SELECT fp.id, fp.date, fp.acct_num, fp.acct_name, fp.symbol, fp.company, fp.price, fp.pchange, fp.quantity, fp.pct_of_acct,
CASE WHEN fp.cost_basis_per_share='--' THEN sm.basis_price ELSE fp.cost_basis_per_share END as cost_basis_per_share,
CASE WHEN fp.total_gl='--' THEN fp.current_val - sm.basis_price*fp.quantity ELSE fp.total_gl END as total_gl,
CASE WHEN fp.total_gl_p='--' THEN (fp.current_val - sm.basis_price*fp.quantity)/(sm.basis_price*fp.quantity)*100 ELSE fp.total_gl_p END as total_gl_p,
CASE WHEN fp.cost_basis='--' THEN sm.basis_price*fp.quantity ELSE fp.cost_basis END as cost_basis,
fp.today_gl, fp.today_gl_p, fp.current_val,
DATEDIFF(fp.date, sm.basis_date)/365 as holding_time
FROM fidelity_positions fp
LEFT JOIN security_metas sm on sm.symbol = fp.symbol
-- WHERE fp.status = 'A' AND sm.status = 'A'
ORDER BY fp.id
