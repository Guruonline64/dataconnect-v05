<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $pdo=db();
$s=$pdo->prepare('SELECT balance FROM wallets WHERE user_id=?');$s->execute([$u['id']]);$w=$s->fetch();
$s=$pdo->prepare("SELECT COUNT(*) FROM share_holdings WHERE user_id=? AND status='active'");$s->execute([$u['id']]);$shares=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");$s->execute([$u['id']]);$unread=(int)$s->fetchColumn();
out(true,'OK',['user'=>$u,'wallet_balance'=>(float)($w['balance']??0),'active_shares'=>$shares,'unread_notifications'=>$unread]);
