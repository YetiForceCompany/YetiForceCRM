<?php

/**
 * demo for accessing Yetiforce REST interface with yetirest class
 * @license YetiForce Public License 1.1
 * @author Alex Weber <migoi.snow@gmail.com>
 * @version 1.0
 */

if (php_sapi_name() !== 'cli') {
  die('This should only be called from console');
}

require_once('yetirest.php');

$host = 'http://yeti.local/';
// created with settings->integration->webservice applications
$wsname = 'apiuser';
$wspass = 'apipass';
$wstoken = 'PYxjkIFbV6SiMylL9UZoN5Kcgt7nsRhu';

// created with settings->integration->webserice users
$user = 'demo@yetiforce.com';
$pass = 'demo';

// demo module and record id
$module = 'Calendar';
$id = 10144;

// create object
$yr = new YetiRest($host, $wsname, $wspass, $wstoken); 
if ($yr->login($user, $pass)) {  // logging into webservice

  // get a list of supported modules
  echo 'modules'.PHP_EOL;
  $data = $yr->listModules();
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;

  // get a list of available methods
  echo 'methods'.PHP_EOL;
  $data = $yr->listMethods();
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;

  // show privileges for module
  echo 'privileges for module '.$module.PHP_EOL;
  $data = $yr->privileges($module);
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;

  // show possible fields for module
  echo 'fields for module '.$module.PHP_EOL;
  $data = $yr->fields($module);
  foreach($data as $field) {
    echo ' '.$field['name'];
    if ($field['mandatory']) {
      echo ' *';
    }
    echo PHP_EOL;
  }
  echo '************************'.PHP_EOL.PHP_EOL;

  // show the hierarchy
  echo 'hierarchy for module '.$module.PHP_EOL;
  $data = $yr->hierarchy($module);
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;
  // fetch a specific record
  echo 'record with id #'.$id.' for module '.$module.PHP_EOL;
  $data = $yr->getRecord($module, $id);
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;
die;

  // get multiple records with one call
  $limit = 5;
  $offset = 0;
  echo 'list records for '.$module.PHP_EOL;
  $data = $yr->listRecords($module, $limit, $offset);
  print_r($data);
  echo '************************'.PHP_EOL.PHP_EOL;

  // update a record
  $recorddata = array(
      'accountname' => 'JAC - Just Another Customer',
      'active' => 0
  );
  echo 'updating record';
  $response = $yr->updateRecord($module, $id, $recorddata);
  print_r($response);

  // create new record
  $recorddata = array(
    'accountname' => 'Foobar',
    'active' => 1,
    // rejected PR for userid
    // 'assigned_user_id' => $yr->userId, // preset current users id based on login information
  );
  echo 'create new record';
  $response = $yr->createRecord($module, $recorddata);
  print_r($response); // return the id of the new record
  echo '************************'.PHP_EOL.PHP_EOL;

  if (!$yr->logout()) {
    echo 'uuups, tried to logout, but session still alive...';
  }
}

?>
