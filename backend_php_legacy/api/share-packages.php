<?php
require __DIR__.'/_bootstrap.php';
require_user();
$s=db()->query('SELECT id,name,investment_amount,daily_return,duration_days FROM share_packages WHERE active=1 ORDER BY investment_amount');
out(true,'OK',['packages'=>$s->fetchAll()]);
