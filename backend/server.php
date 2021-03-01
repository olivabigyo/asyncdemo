<?php

// This is a .php data located on the backend computer
// It has 3 small parts:
// - setup headers and parsing what type of action is required from us
// - setup pdo
// - actual request handling and creating response

// In a bigger project these would be in diffenrent files

// ---------------------------------------------------------------------------
// 1. Part - Headers

// The POST type fetch is always two steps:
// - The first step is just like checking if it is allowed to us to send POST
//   "Hey, server, what are the options by you? I want to send you a POST request! Are you ok with that?"
//   In the first round the browser set the request_method to OPTIONS and it gets back a status (200:ok) and headers
// - The second step is the actual POST request
//   In the second round the browser sends the POST request with data and server responding to it.

// (Note: this is two step because we use the POST method for all of our fetch()es.
//        For GETs the first step is omitted.)


// These are CORS Headers
// TODO: What is CORS?

// TODO: only allow olivabigyo.github.io
//header('Access-Control-Allow-Origin: https://olivabigyo.github.io');

// With these headers in the resonse the server says to the browser:
// we handle requests from anyone
header('Access-Control-Allow-Origin: *');
// we will handle only POST requests but not GET, PUT, DELETE request...
header('Access-Control-Allow-Methods: POST');
// The browser/client is allowed to change the Content Type header
header('Access-Control-Allow-Headers: Content-Type');
// Our response will be a JSON data
header('Content-Type: application/json');


// First round, if the method is OPTIONS:
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    // We have set all the necessary CORS headers, just return.
    // I this case the browser gets the status and the headers.
    exit;
}

// This is just a helper function to give feedback and exit
// In our response (json data) we set `ok` to false and give the error message on the `error` key
function exitWithError($errorMessage)
{
    echo json_encode(array('ok' => false, 'error' => $errorMessage));
    exit;
}

// Second round, we will handle only POST requests
// otherwise return with error feedback
if ($method !== 'POST') {
    exitWithError('Only POST requests allowed');
}

// At this point we know we get a POST request

// Parse the client request.
// The client request is in JSON,
// so we cannot use the usual $_POST superglobal like we do by submitted html forms in a CMS project
// we have to read the php input file and parse/decode the json data ourselves
$request = json_decode(file_get_contents('php://input'));


// we read from the json data what is the operation name it wants us to do
// the arrow -> is like the . operator was in javascript
$action = $request->action;

// in this small project we handle only two operations
// otherwise return with error
if ($action !== 'getMessages' && $action !== 'addMessage') {
    exitWithError('Unrecognized action');
}

// ------------------------------------------------------------------
// 2. Part - Setup PDO

// At this point we know that we have a POST request
// we read from the json data that the required action is getMessages or addMessages
// Up to this point it wasn't necessary to make a connection to our database

// We make the new PDO instance only now to undestand the steps
// In a bigger project the database connection would be in an other file eg. credentials.php
// and it would be required file at the top of this file

// Setting up the variables for the connection
$db_user  = 'root';
$db_pass  = '';
$db_name  = 'asyncdemo';
$db_host  = 'localhost';
$db_port  = 3306; // MySQL default port
$db_options = array(
    // we want to be able catch the pdo errors
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // we want to get assoc arrays
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$DSN = "mysql:host={$db_host}; port={$db_port}; dbname={$db_name}; charset=utf8mb4";

try {
    $conn = new PDO($DSN, $db_user, $db_pass, $db_options);
} catch (PDOException $e) {
    // the catch part is always error handling
    // usually return/exit with feedback
    exitWithError("Database connection failed: " . $e->getMessage());
}


// ------------------------------------------------------------------
// 3. Part - Handling the request

// yay! we actually do something now
// Up to this point it was just exits with a header :)

// Our POST request has an action parameter as geMessager or addMessages

// handling the getMessages request
if ($action === 'getMessages') {
    // We'll be collecting messages in this array
    $messages = array();

    try {
        // this is a query without client side parameters, we don't need to make prepared statement
        // a simple query is enough
        $stmt = $conn->query('SELECT * FROM messages ORDER BY id DESC LIMIT 10');
        // note that we ask for the last 10 entry in descending order
        // we will get the last(and newest) 'id' first

        while ($row = $stmt->fetch()) {
            // we want to save the fetched rows in reverse order
            // so it is easy to display the newest chat message last at the bottom of the chat app
            // Option: we could have handle this clientside in the code.js reversing the response array
            // in that case it would be here a simple: $messages[]=$row; without unshift
            array_unshift($messages, $row);
        }
    } catch (PDOException $e) {
        // error handling: return with feedback
        exitWithError("SELECT failed: " . $e->getMessage());
    }

    // our response is a JSON object with fields 'ok' and the fetched array as 'messages'
    // tricky Q: which does what  echo, echo and exit or return  ???
    echo json_encode(array('ok' => true, 'messages' => $messages));
    exit;
}
// this is not a function it don't returns
// it writes output by echoing the fetched data after the headers
// exit is optional, because we have only an other if statement after this file in which we won't enter
// but it is better to exiting now


// handling the addMessages request
if ($action === 'addMessage') {
    try {
        // we have client side parameters in this query, we need to use prepared statement
        // we use :placeholders at preparing the query
        // it could be made the short way with VALUES (?,?)
        $stmt = $conn->prepare('INSERT INTO messages (name, content) VALUES (:name, :content)');

        // Option A:
        // because we know that the payload has a name key and a content key
        // by executing the prepared statement we present an array to populate the placeholders
        // again: -> is like . in JS
        // we convert the json object to array
        $parametersToBind = (array) $request->payload;
        $stmt->execute($parametersToBind);

        // Option B: Alternatively
        // $payload = $request->payload;
        // $stmt->bindValue(':name', $payload->name);
        // $stmt->bindValue(':content', $payload->content);
        // $stmt->execute();
    } catch (PDOException $e) {
        exitWithError("INSERT failed: " . $e->getMessage());
    }
    // We only return an ok
    // we could have return the new id but let's keep it simple...
    echo json_encode(array('ok' => true));
    exit;
}
