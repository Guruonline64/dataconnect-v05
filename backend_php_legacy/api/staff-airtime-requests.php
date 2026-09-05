<?php
require __DIR__.'/_bootstrap.php';
$staff=require_role(['dispenser','staff','admin']);
$s=db()->query("SELECT ar.id,ar.network,ar.amount,ar.recipient_phone,ar.status,ar.created_at,u.id user_id,u.username,u.phone user_phone
                FROM airtime_requests ar JOIN users u ON u.id=ar.user_id
                WHERE ar.status='pending' ORDER BY ar.id ASC LIMIT 100");
out(true,'OK',['requests'=>$s->fetchAll()]);
