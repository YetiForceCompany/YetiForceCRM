<?php
set_include_path(get_include_path() . PATH_SEPARATOR . ".". PATH_SEPARATOR . "/var/www/yeti");
require_once('yetirest.php');
include_once('procrm_users.php');

$options = array(
  'mssql' => array(
      'host' => '192.168.3.2',
      'options' => array(
        'Database' => 'MarketingByCallSQL',
        'Uid' => 'sa',
        'PWD' => '3x3istNeun',
        "CharacterSet" => "UTF-8"
      ),
  ),
  'yeti' => array(
    // 'host' => "http://18.117.121.63/",
    'host' => "http://127.0.0.1/",
    'wsname' => 'portalsrv',
    'wspass' => 'portalpass',
    // 'wstoken' => 'TDmqFYoPJLzIXZCxcEjuyQBr3vUVhi9N',
    'wstoken' => 'PDSMYObfXAvKla0V29mReITQhqZCHycg',
    'user' => 'demo@yetiforce.com',
    'pass' => 'demo',
  )
);


function connect_mssql($options) {
  $conn = sqlsrv_connect($options['mssql']['host'], $options['mssql']['options']);
  if (!$conn) {
    var_export(sqlsrv_errors());
  }
  return $conn;
}

function formatErrors( $errors ) {
  echo "Error information: ".PHP_EOL;
  if ($errors) {
    foreach ( $errors as $error ) {
      echo "SQLSTATE: ".$error['SQLSTATE'].PHP_EOL;
      echo "Code: ".$error['code'].PHP_EOL;
      echo "Message: ".$error['message'].PHP_EOL;
    }
  }
}

function get_bearbeiter($conn) {
  $tsql = 'SELECT TOP(5) Bearbeiter, Vorname, Aktiv '
        .' FROM Bearbeiter '
        ;
  $result = sqlsrv_query($conn, $tsql);
  if ($result == FALSE) {
    die(formatErrors(sqlsrv_errors()));
  }
  return $result;
}

function get_addresses($conn) {
  $tsql = 'SELECT TOP(50)'
          .'Adressen.KontaktID, Adressen.KundenNummer, Adressen.Name, Adressen.Strasse, Adressen.Branche '
          .', Adressen.PLZ, Adressen.Ort, Adressen.Telefon, Adressen.Fax, Adressen.Email '
          .', Adressen.web, Adressen.memo, Adressen.gesperrt '
          .'FROM Adressen '
          .'WHERE Adressen.KontaktID = 24 AND Adressen.KontaktID IN ('
          .'  SELECT  Kontakthistorie.KontaktID '
          .'  FROM Kontakthistorie '
          .'  GROUP BY Kontakthistorie.KontaktId) '
          .'ORDER BY Adressen.KontaktID '
          ;
  // echo ('Reading data from table' . PHP_EOL);
  // echo ('SQL query: '.$tsql.PHP_EOL);
  $result = sqlsrv_query($conn, $tsql);
  if ($result == FALSE) {
    die(formatErrors(sqlsrv_errors()));
  }
  return $result;
}

function get_contact_person($conn, $contact_id) {
  $tsql = 'SELECT  '
          .'KonAPID, AP_Anrede, AP_Name, AP_Vorname, AP_Funktion, ap_email, ap_telefon, ap_fax '
          .'FROM Anpsprechpartner '
          .'WHERE KontaktID ='.$contact_id;
  // 	echo ('Reading data from table' . PHP_EOL);
  // 	echo ('SQL query: '.$tsql.PHP_EOL);
  $result = sqlsrv_query($conn, $tsql);
  if ($result == FALSE) {
    die(formatErrors(sqlsrv_errors()));
  }
  return $result;
}

function get_historie($conn, $contact_id) {
  $tsql = 'SELECT  '
          .'KonVerwID, Datum, WVDatum, WVErledigt, Bemerkungen, TerminDatum, TerminErledigt '
          .', Ergebnis, Status, Ansprechpartner, Bearbeiter '
          .'FROM Kontakthistorie '
          .'WHERE KontaktID ='.$contact_id;
  // 	echo ('Reading data from table' . PHP_EOL);
  // 	echo ('SQL query: '.$tsql.PHP_EOL);
  $result = sqlsrv_query($conn, $tsql);
  if ($result == FALSE) {
    die(formatErrors(sqlsrv_errors()));
  }
  return $result;
}



function comment_exists($history_id) {
  global $crmobject;
  $wsquery = "SELECT * FROM ModComments WHERE histid='".$history_id."' LIMIT 1;";
  $record = $crmobject->query($wsquery);
  if(isset($record[0])) {
    return $record[0];
  }
}


function add_account($yr, $row) {
  global $crmobject;
  $streetinfo = array('strasse' => '', 'hnr' => '');
  preg_match_all("/([a-zA-Z\s\.\-\ßäöüÄÖÜ]+)\s(.*[0-9]+.*)/is", $row['Strasse'], $parts, PREG_SET_ORDER);
  if (isset($parts[0]) && isset($parts[0][1])) {
    $streetinfo['strasse'] = trim($parts[0][1]);
    if (isset($parts[0][2])) {
      $streetinfo['hnr'] = trim($parts[0][2]);
    }
  }
  $active = 1;
  if ($row['gesperrt'] == 1) {
    $active = 0;
  }
  $data = array(
    'accountname' => $row['Name'], 
    'customerno' => $row['KundenNummer'],
    'kontaktid' => $row['KontaktID'],
    'assigned_user_id' => 14, //Gruppe "unbekannt",
    'description' => $row['memo'],
    'active' => $active,
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
  $response = $yr->createRecord('Accounts', $data);
  $data['id'] = $response['id'];
  return $data;
}

function build_c($yr, $data) {
  $customers = array();
  $contacts = array();
  echo 'Kontakte - Anzahl Bestandsdaten: '.$data['count'].PHP_EOL.PHP_EOL;
  foreach($data['records'] as $id => $entry) {
    $entry['id'] = $id;
    if (!empty($entry['customerno'])) {
      $entry['customerno'] = (int) $entry['customerno'];
      $customers[$entry['customerno']] = $entry;
    }
     if (!empty($entry['kontaktid'])) {
      $contacts[$entry['kontaktid']] = $entry;
    }
  }
  return array($customers, $contacts);
}

function build_p($yr, $data) {
  $persons = array();
  echo 'Personen - Anzahl Bestandsdaten: '.$data['count'].PHP_EOL.PHP_EOL;
  foreach($data['records'] as $id => $entry) {
    $entry['id'] = $id;
    $persons[$entry['apnr']] = $entry;
  }
  return $persons;
}

function build_m($yr, $data) {
  $modcomments = array();
  echo 'Kommentare - Anzahl Bestandsdaten: '.$data['count'].PHP_EOL.PHP_EOL;
  foreach($data['records'] as $id => $entry) {
    $entry['id'] = $id;
    $hits = false;
    preg_match('/\[H#(\d+)\]$/', $entry['commentcontent'], $hits);
    if (isset($hits[1])) {
      $histid = $hits[1];
      $modcomments[$histid] = $entry;
    }
  }
  return $modcomments;
}

function build_a($yr, $data) {
  $activities = array();
  echo 'Aktivitäten - Anzahl Bestandsdaten: '.$data['count'].PHP_EOL.PHP_EOL;
  foreach($data['records'] as $id => $entry) {
    $entry['id'] = $id;
    $hits = false;
    preg_match('/\[H#(\d+)\]$/', $entry['description'], $hits);
    if (isset($hits[1])) {
      $histid = $hits[1];
      $activities[$histid] = $entry;
    }
  }
 return $activities;
}




function umlauts($string) {
  $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´");
  $replace = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "");
  return str_replace($search, $replace, $string);
}

function build_short($firstname, $lastname) {
  $f = umlauts($firstname);
  $l = umlauts($lastname);
  return strtolower(substr($f, 0, 2)).strtolower(substr($l, 0, 2));
}


// -oauto_cache,reconnect,defer_permissions -o Ciphers=arcfour
// -o cache=yes -o kernel_cache -o compression=yes  -o large_read -o Ciphers=arcfour
// sshfs ops@18.117.121.63:/var/www/yeti /home/xweber/mnt/  -o cache=yes -o kernel_cache -o compression=no -o large_read -o Ciphers=aes128-gcm@openssh.com -o big_writes -o auto_cache


$mssql = connect_mssql($options);
if (!$mssql) {
  die('keine Verbindung zum MSSQL Server'.PHP_EOL);
}
$yr = new YetiRest($options['yeti']['host'], $options['yeti']['wsname'], $options['yeti']['wspass'], $options['yeti']['wstoken']);
if ($yr->login($options['yeti']['user'], $options['yeti']['pass'])) {
  // $yr->debug = true;

  echo 'Start...'.PHP_EOL.PHP_EOL;

  $data = $yr->listRecords('Accounts', 10000, 0, ['accountname', 'kontaktid', 'customerno']);
  list($customers, $contacts) = build_c($yr, $data);
  $data = $yr->listRecords('Contacts', 10000, 0, ['apnr', 'active', 'customerno']);
  $persons = build_p($yr, $data);
  $data = $yr->listRecords('ModComments', 10000, 0);
  $modcomments = build_m($yr, $data);
  $data = $yr->listRecords('Calendar', 10000, 0, ['description']);
  $activities = build_a($yr, $data);
  $users = build_users();

  $results = get_addresses($mssql);
  $i = 1;

  while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
    echo '---------------'.PHP_EOL;
    echo('#'.$i.' '.$row['Name'].' ('.$row['KontaktID'].') -> ['.$row['KundenNummer'].']'.PHP_EOL);
    $account = false;
    $row['KontaktID'] = (int) $row['KontaktID'];
    if ($row['KundenNummer'] == 0 || $row['KundenNummer'] == '0') {
      $row['KundenNummer'] = '';
    }

    if (isset($row['KontaktID']) && isset($contacts[$row['KontaktID']])) {
      $account = $contacts[$row['KontaktID']]; 
      // echo ' bei Kontakt gefunden'.PHP_EOL;
      if (empty($account['customerno']) && !empty($row['KundenNummer'])) {
        echo '  Kontakt braucht update'.PHP_EOL;
        $success = $yr->updateRecord('Accounts', $account['id'], array('assigned_user_id' => 14,'customerno' => $row['KundenNummer']));
        $account['customerno'] = $row['KundenNummer'];
      }
    }
    if (isset($customers[$row['KundenNummer']]) && isset($customers[$row['KundenNummer']])) {
      $account = $customers[$row['KundenNummer']];
      // echo ' bei Kunde gefunden'.PHP_EOL;
      if (empty($account['kontaktid']) && !empty($row['KontaktID'])) {
        echo ' Kunde braucht update'.PHP_EOL;
        $success = $yr->updateRecord('Accounts', $account['id'], array('assigned_user_id' => 14,'kontaktid' => $row['KontaktID']));
        $account['kontaktid'] = $row['KontaktID'];
      }
    }
    if (!$account) {
      echo " lege Account an...".PHP_EOL;
      $account = add_account($yr, $row);
      if(!empty($row['KontaktID'])) {
        $contacts[$row['KontaktID']] = $account;
      }
      if(!empty($row['customerno'])) {
        $contacts[$row['customerno']] = $account;
      }
    }

    // Ansprechpartner
    $contact_persons = get_contact_person($mssql, $account['kontaktid']);
    while ($partner = sqlsrv_fetch_array($contact_persons, SQLSRV_FETCH_ASSOC)) {
      if(!isset($persons[$partner['KonAPID']])) {
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
          'assigned_user_id' => 14, // Gruppe "unbekannt" $yr->userId
        );
        echo '  about to create:'.PHP_EOL;
        $yr->createRecord('Contacts', $contactData);
      } else {
        echo ' - Ansprechpartner "'.$partner['AP_Name'].'" existiert bereits.'.PHP_EOL;
      }
    }
    sqlsrv_free_stmt($contact_persons);

    // Kommentare
    $historie = get_historie($mssql, $account['kontaktid']);
    // print_r($modcomments);
    while ($eintrag = sqlsrv_fetch_array($historie, SQLSRV_FETCH_ASSOC)) {
      // var_dump($eintrag['KonVerwID']);
      $userid = 15;
      if (isset($users[$eintrag['Bearbeiter']])) {
        $user = $users[$eintrag['Bearbeiter']];
        $userid = $user['id'];
        // echo ' User gefunden: '.$user['last_name'].' hat die ID '.$user['id'].PHP_EOL;
      } else {
        echo '  *** Kein User gefunden zu: "'.$eintrag['Bearbeiter'].'" ***'.PHP_EOL;
        // var_export($users);
        die();
      }
      // continue;



      if (!isset($modcomments[$eintrag['KonVerwID']])) {
        // var_dump($eintrag);
        if (!empty($eintrag['Bemerkungen'])) {
          $activityData  = array(
            'commentcontent' => $eintrag['Bemerkungen']. ' [H#'.$eintrag['KonVerwID'].']',
            'assigned_user_id' => $userid,
            'related_to' => $account['id'],
            'created_user_id' => $userid,
            'userid' => $userid,
            'createdtime' => date_format($eintrag['Datum'], 'd.m.Y h:i:s'),
          );
          echo ' - erstelle Kommentar #'.$eintrag['KonVerwID'].PHP_EOL;
          $yr->createRecord('ModComments', $activityData);
        }
      }
      if (!isset($activities[$eintrag['KonVerwID']])) {
        if (!empty($eintrag['WVDatum'])) {
          $istdone = null;
          if ($eintrag['WVErledigt'] == 1) {
            $isdone = 'completed';
          }
          $notificationData = array(
            'assigned_user_id' => $userid,
            'created_user_id' => $userid,
            'createdtime' => date_format($eintrag['Datum'], 'd.m.Y h:i:s'),
            'activitytype' => 'Task',
            'date_start' => date_format($eintrag['WVDatum'], 'd.m.Y'),
            'time_start' => date_format($eintrag['WVDatum'], 'h:i:s'),
            'time_end' => date_format($eintrag['WVDatum'], 'h:i:s'),
            'due_date' => date_format($eintrag['WVDatum'], 'd.m.Y'),
            'subject' => 'WV: '.$row['Name'],
            'description' => $eintrag['Bemerkungen'],
            'activitystatus' => $isdone,
          );
          echo ' - erstelle Wiedervorlage #'.$eintrag['KonVerwID'].PHP_EOL;
          $yr->createRecord('Calendar', $notificationData);
          break;
        }
        if (!empty($eintrag['TerminDatum'])) {
          // var_dump($eintrag);
          $istdone = null;
          if ($eintrag['TerminErledigt'] == 1) {
            $isdone = 'completed';
          }
          $extra = '';
          if (!empty($eintrag['Ansprechpartner'])) {
            $extra = '  >  '.$eintrag['Ansprechpartner'];
          }
          $notificationData = array(
            'assigned_user_id' => $userid,
            'created_user_id' => $userid,
            'createdtime' => date_format($eintrag['Datum'], 'd.m.Y h:i:s'),
            'activitytype' => 'Meeting',
            'date_start' => date_format($eintrag['TerminDatum'], 'd.m.Y'),
            'time_start' => date_format($eintrag['TerminDatum'], 'h:i:s'),
            'time_end' => date_format($eintrag['TerminDatum'], 'h:i:s'),
            'due_date' => date_format($eintrag['TerminDatum'], 'd.m.Y'),
            'subject' => 'Termin: '.$row['Name'],
            'description' => $eintrag['Bemerkungen'].$extra,
            'activitystatus' => $isdone,
          );
          // print_r($notificationData);
          echo ' - erstelle Termin #'.$eintrag['KonVerwID'].PHP_EOL;
          $yr->createRecord('Calendar', $notificationData);
        }
      }
      break;
    }
  }

  if (!$yr->logout()) {
    echo 'uuups, session still alive';
  }
}

?>
