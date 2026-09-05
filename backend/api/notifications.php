<?php
require __DIR__.'/_bootstrap.php';
$u=require_user(); $s=db()->prepare('SELECT id,title,body,is_read,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100'); $s->execute([$u['id']]);
out(true,'OK',['notifications'=>$s->fetchAll()]);
