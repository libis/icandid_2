<?php

$config = Array();

function init() {
    global $config;
    $env = parse_ini_file('.env');
    $list = ['icandidApiUrl','elasticUrl','elasticUser','elasticPassword'];

    foreach($list as $k => $v) {
        if (getenv($v)) {
            $config[$v] = getenv($v);
        } else {
            if (getenv(strtoupper($v))) {
                $config[$v] = getenv(strtoupper($v));
            } else {
                $config[$v] = $env[$v];
            }
        }
    }
}

function getPermissions($apikey) {
    global $config;
    $permissions = get_cache($apikey);
    if (!$permissions) {
        $ch = curl_init($config['icandidApiUrl'] . "/" . $apikey); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        
        $output = curl_exec($ch);
    
        if (curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200) {
            $permissions = json_decode($output);
            save_cache($permissions, $apikey, 600);
        } else {
            http_response_code(401);  // unauthorised
            exit;
        }
    }
    return $permissions;

}

function adaptQuery($entityBody, $allowedDatasets) {
    $query=json_decode('
    {
        "query" : {
            "bool":{
                "must": [
                    {
                        "terms": {
                            "isBasedOn.isPartOf.@id": []
                        }
                    }
                ]
            }
    
        }
    }
    ');

    $query->query->bool->must[0]->terms->{'isBasedOn.isPartOf.@id'} = $allowedDatasets;
    if (isset($entityBody->query)) $query->query->bool->must[] = $entityBody->query;
    $entityBody->query = $query->query;
    return $entityBody;
}

function getResult($query, $request_uri) {
    global $config; //elasticUrl, $elasticPassword, $elasticUser;
    
    $ch = curl_init($config['elasticUrl'] . $request_uri);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $config['elasticUser'] . ":" . $config['elasticPassword']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $result = curl_exec($ch);
    $result_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    curl_close($ch);

    return array($result, $result_code);
    
}

function writelog($d) {
    if(is_array($d) || is_object($d) ) {
        $str = json_encode($d, JSON_PRETTY_PRINT);
    } else {
        $str = $d;
    }
    $str = date('r') . " " . $str . "\n";
    file_put_contents("logs/proxy.log", $str, FILE_APPEND);

}

?>