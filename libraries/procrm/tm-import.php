<?php
set_include_path(get_include_path() . PATH_SEPARATOR . ".". PATH_SEPARATOR . "/var/www/yeti");
require_once('remoteaccess.php');


$options = array(
  'serverName' => '192.168.3.2',
  'connectionOptions' => array(
      'Database' => 'MarketingByCallSQL',
      'Uid' => 'sa',
      'PWD' => '3x3istNeun',
      "CharacterSet" => "UTF-8"
  ),
  'endpointUrl' => 'http://18.117.121.63/api/webservice.php',
  'userName' => 'admin',
  'userAccessKey' => 'a3R6BhsvWmON5tM',
);

function connect_mssql($options) {
  $conn = sqlsrv_connect($options['serverName'], $options['connectionOptions']);
  if($conn) {
    echo 'Connected!'.PHP_EOL;
  }
  else {
    echo 'Connection failed to mssql'.PHP_EOL;
  }
  return $conn;
}

function FormatErrors( $errors ) {
  echo "Error information: ".PHP_EOL;
  if ($errors) {
    foreach ( $errors as $error ) {
      echo "SQLSTATE: ".$error['SQLSTATE'].PHP_EOL;
      echo "Code: ".$error['code'].PHP_EOL;
      echo "Message: ".$error['message'].PHP_EOL;
    }
  }
}

function get_data($conn) {
  $tsql = 'SELECT TOP(5)'
          .'Adressen.KontaktID, Adressen.KundenNummer, Adressen.Name, Adressen.Strasse, Adressen.Branche '
          .', Adressen.PLZ, Adressen.Ort, Adressen.Telefon, Adressen.Fax, Adressen.Email '
          .', Adressen.web, Adressen.memo '
          .'FROM Adressen '
          .'WHERE Adressen.KontaktID = 24 '
          .'ORDER BY Adressen.KontaktID '
          ;
  // 	echo ('Reading data from table' . PHP_EOL);
  // 	echo ('SQL query: '.$tsql.PHP_EOL);
  $getResults = sqlsrv_query($conn, $tsql);
  if ($getResults == FALSE) {
    die(FormatErrors(sqlsrv_errors()));
  }
  return $getResults;
}

function get_contact_person($conn, $contact_id) {
  $tsql = 'SELECT  '
          .'KonAPID, AP_Anrede, AP_Name, AP_Vorname, AP_Funktion, ap_email, ap_telefon, ap_fax '
          .'FROM Anpsprechpartner '
          .'WHERE KontaktID ='.$contact_id;
  // 	echo ('Reading data from table' . PHP_EOL);
  // 	echo ('SQL query: '.$tsql.PHP_EOL);
  $getResults = sqlsrv_query($conn, $tsql);
  if ($getResults == FALSE) {
    die(FormatErrors(sqlsrv_errors()));
  }
  return $getResults;
}

function get_historie($conn, $contact_id) {
  $tsql = 'SELECT  '
          .'Datum, WVDatum, WVErledigt, Bemerkungen, TerminDatum, TerminErledigt '
          .', Ergebnis, Status, Ansprechpartner '
          .'FROM Kontakthistorie '
          .'WHERE KontaktID ='.$contact_id;
  // 	echo ('Reading data from table' . PHP_EOL);
  // 	echo ('SQL query: '.$tsql.PHP_EOL);
  $getResults = sqlsrv_query($conn, $tsql);
  if ($getResults == FALSE) {
    die(FormatErrors(sqlsrv_errors()));
  }
  return $getResults;
}

function contact_exists($contact_id) {
  global $crmobject;
  $wsquery = "SELECT * FROM Accounts WHERE kontaktid = '".$contact_id."' LIMIT 1;";
  $record = $crmobject->query($wsquery);
  if(isset($record[0])) {
    return $record[0];
  }
}

function customer_exists($customer_id) {
  global $crmobject;
  $wsquery = "SELECT * FROM Accounts WHERE customerno='".$customer_id."' LIMIT 1;";
  $record = $crmobject->query($wsquery);
  if(isset($record[0])) {
    return $record[0];
  }
}

function contact_person_exists($customer_id) {
  global $crmobject;
  $wsquery = "SELECT * FROM Contacts WHERE apnr='".$customer_id."' LIMIT 1;";
  $record = $crmobject->query($wsquery);
  if(isset($record[0])) {
    return $record[0];
  }
}

function comment_exists($history_id) {
  global $crmobject;
  $wsquery = "SELECT * FROM ModComments WHERE histid='".$history_id."' LIMIT 1;";
  $record = $crmobject->query($wsquery);
  if(isset($record[0])) {
    return $record[0];
  }
}


function add_account($row) {
  global $crmobject;
  $streetinfo = array('strasse' => '', 'hnr' => '');
  preg_match_all("/([a-zA-Z\s\.\-\ßäöüÄÖÜ]+)\s(.*[0-9]+.*)/is", $row['Strasse'], $parts, PREG_SET_ORDER);
  if (isset($parts[0]) && isset($parts[0][1])) {
    $streetinfo['strasse'] = trim($parts[0][1]);
    if (isset($parts[0][2])) {
      $streetinfo['hnr'] = trim($parts[0][2]);
    }
  }
  $data = array(
    'accountname' => $row['Name'], 
    'account_no' => $row['KundenNummer'],
    'customerno' => $row['KundenNummer'],
    'kontaktid' => $row['KontaktID'],
    'assigned_user_id' => $crmobject->userId,
    'description' => $row['memo'],
    'active' => 1,
    'addresslevel1a' => 'Deutschland',
    'addresslevel5a' => $row['Ort'],
    'addresslevel7a' => $row['PLZ'],
    'addresslevel8a' => $streetinfo['strasse'],
    'buildingnumbera' => $streetinfo['hnr'],
    'phone' => $row['Telefon'],
    'website' => $row['web'],
    'fax' => $row['Fax'],
    'email1' => $row['Email'],
  );
  $account
   = $crmobject->create('Accounts', $data);
}

function add_contact_person($row) {
  global $crmobject;
}


$crmobject = new CRMwebrequests($options['endpointUrl'], $options['userName'], $options['userAccessKey']);
if ($crmobject->login()) {
  $conn = connect_mssql($options);
  $results = get_data($conn);
  $i = 1;
  while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
    echo('#'.$i.' '.$row['Name'].' ('.$row['KontaktID'].') -> ['.$row['KundenNummer'].']'.PHP_EOL);
    if ($row['KundenNummer'] == 0 || $row['KundenNummer'] == '0') {
      $row['KundenNummer'] = '';
    }

    if (!empty($row['KundenNummer'])) {
      $account = customer_exists($row['KundenNummer']);
    } else {
      $account = contact_exists($row['KontaktID']);
    }

    if (!$account) {
      $account = add_account($row);
    }

    $contact_persons = get_contact_person($conn, $row['KontaktID']);
    while ($partner = sqlsrv_fetch_array($contact_persons, SQLSRV_FETCH_ASSOC)) {
      $contact_person = contact_person_exists($partner['KonAPID']);
      if (!$contact_person) {
        switch ($partner['AP_Anrede']) {
          case 'Herr':
            $salutation = 'Mr.';
            break;
          case 'Frau':
            $salutation = 'Mrs.';
            break;
          default:
            $salutation = '';
            break;
        }
        print_r($account);
        $contactData  = array(
          'apnr' => $partner['KonAPID'], 
          'lastname' => $partner['AP_Name'], 
          'firstname' => $partner['AP_Vorname'], 
          'salutationtype' => $salutation, 
          'jobtitle' => $partner['AP_Funktion'], 
          'phone' => $partner['ap_telefon'], 
          'email' => $partner['ap_email'], 
          'fax' => $partner['ap_fax'], 
          'active' => '1',
          'parent_id' => $account['id'],
          'assigned_user_id' => $crmobject->userId
        );
        print_r($contactData);
        $crmobject->create('Contacts', $contactData);
      }
    }
    sqlsrv_free_stmt($contact_persons);

    $historie = get_historie($conn, $row['KontaktID']);
    while ($eintrag = sqlsrv_fetch_array($historie, SQLSRV_FETCH_ASSOC)) {
      $activityData  = array(
        'commentcontent' => $eintrag['Bemerkungen'],
        'assigned_user_id' => $crmobject->userId,
        'related_to' => $account
        ['id'],
        'created_user_id' => $crmobject->userId,
        'createdtime' => '2017-02-14 16:43:39',
        'modifiedtime' => '2017-02-14 16:43:39',
      );
      $response = $crmobject->create('ModComments', $activityData);
      //var_dump($response);
      break;
    }
    break;
  }
  sqlsrv_free_stmt($historie);
    // $crmobject->logout();
} else {
  echo "Login to Yeti failed";
}


// function main($options) {

// /*
//     [Datum] => DateTime Object
//         (
//             [date] => 2012-03-14 00:00:00.000000
//             [timezone_type] => 3
//             [timezone] => Europe/Berlin
//         )
//     [WVDatum] => DateTime Object
//         (
//             [date] => 2012-03-15 00:00:00.000000
//             [timezone_type] => 3
//             [timezone] => Europe/Berlin
//         )
//     [WVErledigt] => 1
//     [Bemerkungen] => Herr Schnell nicht erreicht. OP Gesamt Terminierung. Vertrag OrgaPLan läuft 2013 aus. Optimierung evtl möglich.
//     [TerminDatum] => 
//     [TerminErledigt] => 0
//     [Ergebnis] => Wiedervorlage
//     [Status] => 
//     [Ansprechpartner] => Herr  Schnell
// )
// */
//         // break;
//         $i++;
//     }
//     sqlsrv_free_stmt($results);
// }
// main($options);
// 



  //l	isttypes
      //l	ist all types accessible by web service
      // 	$listTypes = $crmobject->listTypes();
  // 	echo "Modules which can be reached".PHP_EOL;
  // 	foreach ($listTypes as $key=>$value){
    // 		echo ' ['.$key.'] '.$value.PHP_EOL;
    //
  //}
  // 	echo "-----------".PHP_EOL.PHP_EOL;
  
  // 	describe - list the properties of a specific module
  // $module = 'ModComments';
  // $describe = $crmobject->describe($module);
  // echo $module." Fields:".PHP_EOL;
  // foreach ($describe as $pkey => $value){
  //   //s		how field properties
  //           if ($pkey == 'fields') {
  //     foreach ($describe['fields'] as $fkey => $fvalue){
  //       print_r($fvalue);
  //       echo " ".$fvalue['label']." -> ".$fvalue['name'].PHP_EOL;
  //     }
  //   }
  // }
  // echo "-----------".PHP_EOL.PHP_EOL;
  
  // 	list Calendar Entries (tasks) max. 100 entries, if more than loop
      // 	$module = "ModComments";
  // 	$wsquery = "SELECT * FROM ".$module.";";  //"	where date_start >'2017-01-01' LIMIT 100;  ";
    // $entries = $crmobject->Query($wsquery);
    // echo $module.PHP_EOL;
    // foreach ($entries as $values){
    //     //print_r($values);
    // }
    // echo "-----------".PHP_EOL.PHP_EOL;
    


?>
