<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$s=$pdo=db()->prepare('SELECT id,holding_id,return_date,amount,reference,created_at FROM share_return_ledger WHERE user_id=? ORDER BY id DESC LIMIT 100');
$s->execute([$u['id']]); out(true,'OK',['returns'=>$s->fetchAll()]);
