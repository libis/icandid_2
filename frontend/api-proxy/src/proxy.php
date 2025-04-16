<?php
ini_set('memory_limit','8G');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING) ;
include('functions.php');
include('cache.php');

init();

$timeings = [];

$timeings[] = microtime(True);

$request_uri = $_SERVER["REQUEST_URI"];
writelog($request_uri);

$apikey = getallheaders()["APIKEY"];

if ($apikey == "") {
        $apikey = getallheaders()["Apikey"];
}
writelog($apikey);
$entityBody = json_decode(file_get_contents('php://input'));
$timeings[] = microtime(True);

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
$timeings[] = microtime(True);
list($result,$result_code) = getResult($query,$request_uri);
$timeings[] = microtime(True);
header('Content-Type:application/json');
http_response_code($result_code);
print $result;

$s = "";
for ($i = 1; $i<count($timeings); $i++) {
    $s .= ($timeings[$i] - $timeings[$i-1]) . "  ";
}
writelog($s);
?>
