<?php
require __DIR__.'/_bootstrap.php';
$u=require_user();
$s=db()->prepare('SELECT h.id,h.package_id,h.status,h.purchased_at,p.name,p.investment_amount,p.daily_return,p.duration_days
                  FROM share_holdings h JOIN share_packages p ON p.id=h.package_id
                  WHERE h.user_id=? ORDER BY h.id DESC');
$s->execute([$u['id']]);
out(true,'OK',['holdings'=>$s->fetchAll()]);
