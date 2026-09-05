<?php
require __DIR__.'/_bootstrap.php';
require_role(['dispenser','staff','admin']);
$pdo=db();
$counts=[];
foreach([
 'pending_airtime'=>"SELECT COUNT(*) FROM airtime_requests WHERE status='pending'",
 'pending_withdrawals'=>"SELECT COUNT(*) FROM withdrawal_requests WHERE status='pending'",
 'pending_marketers'=>"SELECT COUNT(*) FROM marketers WHERE status='pending'",
 'pending_data'=>"SELECT COUNT(*) FROM data_orders WHERE status='pending'"
] as $k=>$q){$counts[$k]=(int)$pdo->query($q)->fetchColumn();}
out(true,'OK',['counts'=>$counts]);
