<?php
ini_set('memory_limit','8G');
include('functions.php');
include('cache.php');

init();

$request_uri = $_SERVER["REQUEST_URI"];
$apikey = getallheaders()["APIKEY"];
$entityBody = json_decode(file_get_contents('php://input'));
writelog($request_uri);
writelog($apikey);

if (isset($entityBody->scroll_id)) {
    $query = $entityBody;
} else {
    $permissions = getPermissions($apikey);
//    writelog(getenv("HOSTNAME"));
//    writelog($_SERVER["HTTP_HOST"]);
//    writelog(print_r($permissions, True));
    if (getenv("HOSTNAME") != $_SERVER["HTTP_HOST"] && !$permissions->api) {
        http_response_code(401);
        exit();
    }

    $query = adaptQuery($entityBody, $permissions->datasets);
}

list($result,$result_code) = getResult($query,$request_uri);

header('Content-Type:application/json');
http_response_code($result_code);
print $result;

?>
