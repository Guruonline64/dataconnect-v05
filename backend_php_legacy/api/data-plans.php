<?php
require __DIR__.'/_bootstrap.php';
require_user();
$s=db()->query('SELECT id,network,name,price,data_amount,validity_days,active FROM data_plans WHERE active=1 ORDER BY network,price');
out(true,'OK',['plans'=>$s->fetchAll()]);
