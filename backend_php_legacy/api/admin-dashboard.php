<?php
require __DIR__.'/_bootstrap.php';
$admin=require_role(['admin','staff']);
$pdo=db();
$counts=[];
$queries=[
 'users'=>'SELECT COUNT(*) FROM users',
 'customers'=>'SELECT COUNT(*) FROM users WHERE role="customer"',
 'shareholders'=>'SELECT COUNT(DISTINCT user_id) FROM share_holdings WHERE status IN ("pending","active")',
 'marketers'=>'SELECT COUNT(*) FROM marketers',
 'pending_withdrawals'=>'SELECT COUNT(*) FROM withdrawal_requests WHERE status="pending"',
 'pending_airtime'=>'SELECT COUNT(*) FROM airtime_requests WHERE status="pending"',
 'pending_data'=>'SELECT COUNT(*) FROM data_orders WHERE status="pending"',
 'active_shares'=>'SELECT COUNT(*) FROM share_holdings WHERE status="active"'
];
foreach($queries as $k=>$q){$counts[$k]=(int)$pdo->query($q)->fetchColumn();}
$wallet=(float)$pdo->query('SELECT COALESCE(SUM(balance),0) FROM wallets')->fetchColumn();
$sales=(float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM data_orders WHERE status="successful" AND DATE(created_at)=CURDATE()')->fetchColumn();
$recent=$pdo->query('SELECT l.id,l.type,l.amount,l.reference,l.description,l.created_at,u.username,u.phone FROM wallet_ledger l JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 12')->fetchAll();
out(true,'OK',['admin'=>$admin,'counts'=>$counts,'total_wallet_balance'=>$wallet,'today_data_sales'=>$sales,'recent_transactions'=>$recent]);
