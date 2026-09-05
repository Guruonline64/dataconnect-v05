<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $s=db()->prepare('SELECT id,type,amount,reference,description,created_at FROM wallet_ledger WHERE user_id=? ORDER BY id DESC LIMIT 100'); $s->execute([$u['id']]);
out(true,'OK',['transactions'=>$s->fetchAll()]);
