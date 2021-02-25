<?php
// TODO: only allow olivabigyo.github.io
//header('Access-Control-Allow-Origin: https://olivabigyo.github.io');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    // We have set all the necessary CORS headers, just return.
    exit;
}

if ($method !== 'POST') {
    echo(json_encode(array('ok' => false, 'error' => 'Only POST requests allowed')));
    exit;
}

function message($id, $name, $content)
{
    return array('id' => $id, 'name' => $name, 'content' => $content);
}

$messages = array(
    message(1, 'Kata', 'Hoi Zämme!'),
    message(2, 'Misi', 'Neked is hoi!')
);

echo(json_encode(array('ok' => true, 'messages' => $messages)));
