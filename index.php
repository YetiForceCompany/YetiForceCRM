<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

require_once 'include/ConfigUtils.php';
\App\Process::$startTime = microtime(true);
\App\Process::$requestMode = 'WebUI';

$config = [
    'baseURL' => \AppConfig::main('site_URL'),
];

if(!empty($argv)){
  foreach($argv as $argument){
    if($argument==='--dev'){
      // if we are inside dev mode (quasar dev) then return original index.html from webpack dev server
      $response = (new \GuzzleHttp\Client())->request('GET', 'localhost:8080/index.html');
      $body = $response->getBody();
      header('Access-Control-Allow-Origin: *');
      header('access-control-allow-headers: *');
      header('access-control-allow-methods: GET, POST, PUT, DELETE, OPTIONS');
      echo str_replace('<script data-config></script>','<script data-config-url="'.$config['baseURL'].'">window.CONFIG='.json_encode($config).';</script>',$body);
      exit;
    }
  }
}

$webUI = new \App\WebUI();
$webUI->process();


require ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'index.php';
