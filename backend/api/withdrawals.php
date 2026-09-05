<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$s=db()->prepare('SELECT id,amount,status,reason,reference,created_at,reviewed_at FROM withdrawal_requests WHERE user_id=? ORDER BY id DESC LIMIT 100');
$s->execute([$u['id']]); out(true,'OK',['withdrawals'=>$s->fetchAll()]);
