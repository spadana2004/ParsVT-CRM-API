<?php
include "ParsVTRest/NetClient.php";
$url = "http://yourcrmurl/";
$username = "admin";
$accesskey = "sxGyt6Yu9br1";
$clientInstance = new ParsVT_NetClient($url, $username, $accesskey);
$params = array();
$method = 'ping';
$results        = $clientInstance->doGet($method, $params);

if ($results['status'] == 200) {
    if ($clientInstance->isJson($results['response'])) {
        $response = json_decode($results['response'], true);
        print_r($response);
    }
} else {
    //$error_code = $results['status'];
    //$error_response = $results['response'];
    //$error_errorMessage = $results['errorMessage'];
    print_r($results);
}


$methodparams = array(
    'firstname'=> 'John',
    //'lastname'=> 'Due',
    'assigned_user_id'=> '19x1',
);
$params = array(
    'elementType'=> 'Contacts',
    'element'=> json_encode($methodparams)
);
$method = 'create';
$results        = $clientInstance->doPost($method, $params);
if ($results['status'] == 200) {
    if ($clientInstance->isJson($results['response'])) {
        $response = json_decode($results['response'], true);
        print_r($response);
    }
} else {
    //$error_code = $results['status'];
    //$error_response = $results['response'];
    //$error_errorMessage = $results['errorMessage'];
    print_r($results);
}
