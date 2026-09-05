<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$s=db()->prepare('SELECT id,network,amount,recipient_phone,status,created_at FROM airtime_requests WHERE user_id=? ORDER BY id DESC LIMIT 100');
$s->execute([$u['id']]); out(true,'OK',['requests'=>$s->fetchAll()]);
