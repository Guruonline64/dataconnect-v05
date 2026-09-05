<?php
require __DIR__.'/_bootstrap.php';
require_role(['staff','admin']);
$s=db()->query("SELECT w.id,w.user_id,w.amount,w.status,w.created_at,u.username,u.phone
                FROM withdrawal_requests w JOIN users u ON u.id=w.user_id
                WHERE w.status='pending' ORDER BY w.id ASC LIMIT 100");
out(true,'OK',['withdrawals'=>$s->fetchAll()]);
