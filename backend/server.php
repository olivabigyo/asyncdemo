<?php

// These are CORS Headers
// TODO: only allow olivabigyo.github.io
//header('Access-Control-Allow-Origin: https://olivabigyo.github.io');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');

// This is the type of the server response
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    // We have set all the necessary CORS headers, just return.
    exit;
}

function exitWithError($errorMessage)
{
    echo json_encode(array('ok' => false, 'error' => $errorMessage));
    exit;
}

if ($method !== 'POST') {
    exitWithError('Only POST requests allowed');
}

// Parse the client request. It's in JSON, so we cannot use the usual $_POST:
$request = json_decode(file_get_contents('php://input'));
$action = $request->action;

if ($action !== 'getMessages' && $action !== 'addMessage') {
    exitWithError('Unrecognized action');
}

// ------------------------------------------------------------------
// Setup PDO

$db_user  = 'root';
$db_pass  = '';
$db_name  = 'asyncdemo';
$db_host  = 'localhost';
$db_port  = 3306; // MySQL default port
$db_options = array(
    // Wir wollen in der Testphase wissen, ob es Fehler gibt.
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Mit dieser Option werden die Resultate in Form von assoziativen Arrays retour kommen.
    // In den meisten Fällen ist das der effizienteste Weg, die Resultate in HTML auszugeben ...
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

$DSN = "mysql:host={$db_host}; port={$db_port}; dbname={$db_name}; charset=utf8mb4";

try {
    $conn = new PDO($DSN, $db_user, $db_pass, $db_options);
} catch (PDOException $e) {
    exitWithError("Database connection failed: " . $e->getMessage());
}


// ------------------------------------------------------------------

if ($action === 'getMessages') {
    $messages = array();

    try {
        $stmt = $conn->query('SELECT * FROM messages ORDER BY id DESC LIMIT 14');

        while ($row = $stmt->fetch()) {
            array_unshift($messages, $row);
        }
    } catch (PDOException $e) {
        exitWithError("SELECT failed: " . $e->getMessage());
    }

    echo json_encode(array('ok' => true, 'messages' => $messages));
    exit;
}

if ($action === 'addMessage') {
    $messages = array();

    try {
        $stmt = $conn->prepare('INSERT INTO messages (name, content) VALUES (:name, :content)');
        $stmt->execute((array) $request->payload);
    } catch (PDOException $e) {
        exitWithError("INSERT failed: " . $e->getMessage());
    }

    echo json_encode(array('ok' => true, 'messages' => $messages));
    exit;
}
