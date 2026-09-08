<?php
require __DIR__.'/_bootstrap.php';
require_role(['admin','staff']);
$pdo=db();
$q=trim((string)($_GET['q']??''));
$sql='SELECT u.id,u.phone,u.username,u.role,u.created_at,COALESCE(w.balance,0) balance,\n (SELECT COUNT(*) FROM share_holdings sh WHERE sh.user_id=u.id AND sh.status IN ("pending","active")) share_count,\n (SELECT COUNT(*) FROM withdrawal_requests wr WHERE wr.user_id=u.id AND wr.status="pending") pending_withdrawals\n FROM users u LEFT JOIN wallets w ON w.user_id=u.id';
$params=[];
if($q!==''){$sql.=' WHERE u.username LIKE ? OR u.phone LIKE ?';$params=["%$q%","%$q%"];}
$sql.=' ORDER BY u.id DESC LIMIT 250';
$s=$pdo->prepare($sql);$s->execute($params);
out(true,'OK',['users'=>$s->fetchAll()]);
