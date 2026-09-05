<?php
require __DIR__.'/_bootstrap.php';
require_role(['staff','admin']);
$s=db()->query("SELECT m.id,m.marketer_id,m.name,m.location,m.monthly_pay,m.guarantor_name,m.guarantor_phone,m.status,m.created_at,u.phone
                FROM marketers m JOIN users u ON u.id=m.user_id ORDER BY m.id DESC LIMIT 200");
out(true,'OK',['marketers'=>$s->fetchAll()]);
