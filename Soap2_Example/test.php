<?php
$url = "http://yourcrmurl/";
$ws_username = "admin";
$ws_accessKey = "sxGyt6Yu9br1";
error_reporting(E_ERROR);
echo "<pre>";
if (!extension_loaded('soap')) {
    throw new Exception('SOAP extension not enabled!');
}
$opts = array(
    'http' => array(
        'user_agent' => 'PHPSoapClient'
    )
);
$context = stream_context_create($opts);
$soapClientOptions = array(
    'stream_context' => $context,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'trace' => true
);

//JSON Response
$responsetype = 'json';
try {
    $url = rtrim($url, '/') . '/modules/ParsVT/ws/soap/' . $responsetype . '/?wsdl';
    $client = new SoapClient($url, $soapClientOptions);



    $request1 = $client->ping($ws_username,$ws_accessKey,array());
    $result1 = json_decode($request1, true);
    print_r($result1);

    if ($result1['success']) {
        $methodparams = array(
            'firstname' => 'John',
            'lastname' => 'Due',
            'assigned_user_id' => '19x1',
        );
        $params = array(
            'elementType' => 'Contacts',
            'element' => json_encode($methodparams)
        );

        $request2 = $client->create($ws_username,$ws_accessKey, $params);
        $result2 =  json_decode($request2, true);
        print_r($result2);
        if ($result2['success'] && $result2['result']['id']) {
            $request3 = $client->retrieve($ws_username,$ws_accessKey, array('id' => $result2['result']['id']));
            $result3 =  json_decode($request3, true);
            print_r($result3);
        }
    }
} catch (SoapFault $ex) {
    echo $ex->faultstring;
}

//XML Response
$responsetype = 'xml';
try {
    $url = rtrim($url, '/') . '/modules/ParsVT/ws/soap/' . $responsetype . '/?wsdl';
    $client = new SoapClient($url, $soapClientOptions);
    $xmlresponse1 = $client->ping($ws_username, $ws_accessKey, array());
    print_r($xmlresponse1);
    if ($xmlresponse1->success) {
        $methodparams = array(
            'firstname' => 'John',
            'lastname' => 'Due',
            'assigned_user_id' => '19x1',
        );
        $params = array(
            'elementType' => 'Contacts',
            'element' => json_encode($methodparams)
        );
        $xmlresponse2 = $client->create($ws_username, $ws_accessKey, $params);
        print_r($xmlresponse2);
        if ($xmlresponse2->success && $xmlresponse2->result->id) {
            $xmlresponse3 = $client->retrieve($ws_username, $ws_accessKey, array('id' => $xmlresponse2->result->id));
            print_r($xmlresponse3);
        }
    }
} catch (SoapFault $ex) {
    echo $ex->faultstring;
}
?>
