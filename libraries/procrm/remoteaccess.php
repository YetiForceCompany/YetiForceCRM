<?php
/***********************************************************************************
* CRM Remote Access
* Version: 1.0
* Copyright (C) crm-now GmbH
* All Rights Reserved
* www.crm-now.de
************************************************************************************/

//Include WebRequest performer module class to handle posts and gets
// e.g. from http://pear.php.net/package/HTTP_Client/download/1.2.1/
// here we use the Net_Client class from the CRM
// include_once 'include/Webservices/Relation.php';
include_once 'include/main/WebUI.php';

require_once("/var/www/yeti/vtlib/Vtiger/Net/Client.php");
//Include ZEND Json module (you may use the PHP json functions also)
require_once("/var/www/yeti/include/Zend/Json.php");


class CRMwebrequests {
  var $endpointUrl;
  var $userName;
  var $userKey;
  var $token;
  
  //constructor saves the values
  function __construct($url, $name, $key) {
    $this->endpointUrl=$url;
    $this->userName=$name;
    $this->userKey=$key;
    $this->token=0;
  }

  function getChallenge() {
    //create webrequest performer
    $httpc = new Vtiger_Net_Client($this->endpointUrl);
    //GET request
    $params["operation"]="getchallenge";
    $params["username"]=$this->userName;
    
    $response = $httpc->doGet($params);
    $httpc->disconnect();
    //extract information from response
    $jsonResponse = Zend_JSON::decode($response);
    var_dump($response);
    if($jsonResponse["success"]==false)
    //exit if something went wrong
    die("getChallenge failed:".$jsonResponse["error"]["message"]."<br>");

    $challengeToken = $jsonResponse["result"]["token"];

    return $challengeToken;
  }

  function login() {
    $token = $this->getChallenge();
    $generatedKey = md5($token.$this->userKey);
    $extra = array( 
      "username"=>$this->userName,
      "accessKey"=>$generatedKey
    );
    $jsonResponse = $this->post('login', $extra);
    $sessionId = $jsonResponse["result"]["sessionName"];
    $userId = $jsonResponse["result"]["userId"];
    $this->token=$sessionId;
    $this->userId=$userId;
    return true;
  }

  function post($operation, $additionalParams=array()) {
    $params = array(
      "sessionName" => $this->token,
      "operation" => $operation
    );
    $params = array_merge($params, $additionalParams);
    $httpc = new Vtiger_Net_Client($this->endpointUrl);
    $response = $httpc->doPost($params);
    $jsonResponse = array('success'=>false, 'error'=>array('message'=>''));
    if ($response) {
      $jsonResponse = Zend_JSON::decode($response);
    }
    if($jsonResponse["success"]==false) {
      die("POST operation '".$operation."' failed:".$jsonResponse["error"]["message"].PHP_EOL);
    }
    return $jsonResponse;
  }

  function get($operation, $additionalParams=array()) {
    $params = array(
      "sessionName" => $this->token,
      "operation" => $operation
    );
    $params = array_merge($params, $additionalParams);
    $httpc = new Vtiger_Net_Client($this->endpointUrl);
    $response = $httpc->doGet($params);
    $jsonResponse = array('success'=>false, 'error'=>array('message'=>''));
    if ($response) {
      $jsonResponse = Zend_JSON::decode($response);
    }
    if($jsonResponse["success"]==false) {
      die("GET operation '".$operation."' failed:".$jsonResponse["error"]["message"].PHP_EOL);
    }
    return $jsonResponse;
  }

  function listTypes () {
    $objects = $this->get('listtypes');
    return $objects['result']['types'];
  }

  function describe($moduleType) {
    $extras = array('elementType'=>($moduleType));
    $objects = $this->get('describe', $extras);
    return $objects['result'];
  }

  function query($query) {
    $extras = array('query'=>($query));
    echo $query;
    $objects = $this->get('query', $extras);
    return $objects['result'];
  }

  function retrieve($id) {
    $extras = array('id'=>($id));
    $objects = $this->get('retrieve', $extras);
    return $objects['result'];
  }

  function logout($query) {
    $params = "operation=logout&sessionName=$sessionId";
    $this->get("$endpointUrl?$params");
  }

  function create($moduleType, $data) {
    $jsondata = Zend_Json::encode($data);
    $extra = array(
      'elementType' => $moduleType,
      'element' => $jsondata
    );
    $response = $this->post('put', $extra);
    return $response['result'];
  }

  function update($data) {
    $jsondata = Zend_Json::encode($data);
    $extra = array(
      'element' => $jsondata
    );
    $response = $this->post('update', $extra);
    return $response['result'];
  }

}
?>