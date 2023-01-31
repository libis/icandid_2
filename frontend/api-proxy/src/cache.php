<?php

function save_cache($data, $name, $timeout) {
    $value=array("timeout"=>calc_timeout($timeout),"data"=>$data);
    file_put_contents("./cache/".$name, serialize($value));
}

function get_cache($name) {
    if (file_exists("./cache/".$name)) {
        $value = unserialize(file_get_contents("./cache/".$name));
        if (check_timeout($value["timeout"])) {
            return $value["data"];
        } else {
            return False; // value expired
        }
    } else {
        return False; // no entry found
    }
}


function calc_timeout($int) {
    $timeout=new DateTime(date('Y-m-d H:i:s'));
    date_add($timeout, date_interval_create_from_date_string("$int seconds"));
    $timeout=date_format($timeout, 'YmdHis');

    return $timeout;
}

function check_timeout($timeout) {
    $now=new DateTime(date('Y-m-d H:i:s'));
    $now=date_format($now, 'YmdHis');

    return (intval($now)<intval($timeout));
}

?>