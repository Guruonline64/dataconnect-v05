<?php
declare(strict_types=1);
require_once __DIR__.'/../config/database.php';
header('Content-Type: application/json; charset=utf-8');
try { db()->query('SELECT 1'); echo json_encode(['success'=>true,'message'=>'Data Connect API and MySQL are reachable']); }
catch(Throwable $e){ http_response_code(503); echo json_encode(['success'=>false,'message'=>'Database unavailable']); }
