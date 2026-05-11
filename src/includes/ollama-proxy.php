<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'] ?? '';
if (empty($prompt)) { echo json_encode(['error' => 'Prompt vacio']); exit; }
$ch = curl_init('http://s12_ollama:11434/api/generate');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['model'=>'gemma2:2b','prompt'=>$prompt,'stream'=>false]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);
$resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
echo $err ? json_encode(['error'=>$err]) : $resp;
