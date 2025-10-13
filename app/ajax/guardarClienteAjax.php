<?php
require_once __DIR__ . "/../models/mainModel.php";
use app\models\mainModel;
header('Content-Type: application/json; charset=utf-8');
try {
  $pdo = mainModel::conectar();
  // Recibir JSON (tal como lo envías en fetch)
  $raw  = file_get_contents('php://input');
  $data = json_decode($raw, true);
  $nombre  = strtoupper(trim($data['Nombre']  ?? ''));
  $numero  = trim($data['Numero']  ?? '');
  $email   = trim($data['Email']   ?? '');
  $colonia = strtoupper(trim($data['Colonia'] ?? ''));

  if ($nombre === '' || $numero === '' || $email === '' || $colonia === '') {
    echo json_encode(['success'=>false, 'error'=>'Faltan datos.']); exit;
  }
  // Evitar duplicados por (Nombre+Numero) o Email
  $chk = $pdo->prepare("SELECT Idcliente FROM clientes
                        WHERE (TRIM(Nombre)=:n AND TRIM(Numero)=:num) OR TRIM(Email)=:em
                        LIMIT 1");
  $chk->execute([':n'=>$nombre, ':num'=>$numero, ':em'=>$email]);
  $existe = $chk->fetchColumn();
  if ($existe) {
    echo json_encode(['success'=>true, 'Idcliente'=>(int)$existe, 'msg'=>'Ya existía.']); 
    exit;
  }
  $ins = $pdo->prepare("INSERT INTO clientes (Nombre, Numero, Email, Colonia)
                        VALUES (:n, :num, :em, :co)");
  $ins->execute([':n'=>$nombre, ':num'=>$numero, ':em'=>$email, ':co'=>$colonia]);
  echo json_encode(['success'=>true, 'Idcliente'=>(int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
