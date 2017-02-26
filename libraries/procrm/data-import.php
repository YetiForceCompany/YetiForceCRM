
<?php

include('HTTP/Request2.php');
include('Zend/Json.php');

$options = array(
    'serverName' => '192.168.3.7',
    'connectionOptions' => array(
        'Database' => 'scOffice',
        'Uid' => 'sa',
        'PWD' => '3x3istNeun',
        "CharacterSet" => "UTF-8"
    ),
    'endpointUrl' => 'http://18.117.121.63/webservice.php',
    'userName' => 'admin',
    'userAccessKey' => 'a3R6BhsvWmON5tM',
);

function r($text) {
    $text=trim($text);
    $text = str_replace("&","%26",$text);
    return $text;
}

function connect_mssql($options) {
    $conn = sqlsrv_connect($options['serverName'], $options['connectionOptions']);
    if($conn) {
        echo 'Connected!'.PHP_EOL;
    } else {
        echo 'Connection failed to mssql'.PHP_EOL;
    }
    return $conn;
}

function get_data($conn) {
    $tsql = 'SELECT '
        .'Kontakte.Kurzname, Kontakte.KontaktNummer, Kontakte.Hinweistext, Kontakte.Bemerkungen '
        .', haupt.strasse as hauptstrasse, haupt.Postleitzahl as hauptplz, haupt.ort as hauptort '
	    .', rech.strasse as rechstrasse, rech.Postleitzahl as rechplz, rech.ort as rechort '
	    .', lief.strasse as liefstrasse, lief.Postleitzahl as liefplz, lief.ort as liefort '
        .'FROM Kontakte '
        .'JOIN Anschriften as haupt ON Kontakte.HauptAnschrift = haupt.AnschriftsNummer '
        .'JOIN Anschriften as rech ON Kontakte.StdRechnungsanschrift = rech.AnschriftsNummer '
        .'JOIN Anschriften as lief ON Kontakte.StdLieferanschrift = lief.AnschriftsNummer '
        .'WHERE Kontakte.KontaktNummer >= 100000 '
        .'ORDER BY Kontakte.KontaktNummer '
        ;
    // echo ('Reading data from table' . PHP_EOL);
    // echo ('SQL query: '.$tsql.PHP_EOL);
    $getResults = sqlsrv_query($conn, $tsql);
    if ($getResults == FALSE) {
        die(FormatErrors(sqlsrv_errors()));
    }
    return $getResults;
}

/*
"Kurzname"	"KontaktNummer"	"GeaendertDurch"	"Hinweistext"	"Bemerkungen"	"hauptstrasse"	"hauptplz"	"hauptort"	"rechstrasse"	"rechplz"	"rechort"	"liefstrasse"	"liefplz"	"liefort"
"Domann Michaela"	"100031"	"fischer"	"Servicevertrag TK
20% auf Stundensätze
Zone 2 = 89 €

FIT Update (alle Level)
30 Min. weitere Supportzeit
20% / Zone2
+ DSK 1TB

alt:
-----------------------------------
Servicevertrag IT
Leistungen vor Ort: - 20%
Zone 2 = 89 € garantiert
Leistungen im Haus (FW): Abrechnung erfolgt über Vertrag

FIT IT inkl. 60 Min. SL 1 & 2
20% auf Stundensätze
Zone 2 = 89 €"	""	"Auf der Fels 2"	"67824"	"Feilbingert"	"Auf der Fels 2"	"67824"	"Feilbingert"	"Auf der Fels 2"	"67824"	"Feilbingert"

*/

function get_verbindungen($conn, $contact_no) {
    $tsql = 'SELECT  '
        .'Verbindungsart, Verbindungsnummer '
        .'FROM Verbindungen '
        .'WHERE KontaktNummer ='.$contact_no;
    // echo ('Reading data from table' . PHP_EOL);
    // echo ('SQL query: '.$tsql.PHP_EOL);
    $getResults = sqlsrv_query($conn, $tsql);
    if ($getResults == FALSE) {
        die(FormatErrors(sqlsrv_errors()));
    }
    return $getResults;
}

function convert_entry($results) {
    

}



function FormatErrors( $errors ) {
    /* Display errors. */
    echo "Error information: ";
    if ($errors) {
        foreach ( $errors as $error ) {
            echo "SQLSTATE: ".$error['SQLSTATE'].PHP_EOL;
            echo "Code: ".$error['code'].PHP_EOL;
            echo "Message: ".$error['message'].PHP_EOL;
        }
    }
}

function login($req, $options) {
    $url = $req->getUrl();
    $url->setQueryVariable('operation', 'getchallenge');
    $url->setQueryVariable('username', $options['userName']);

    $resp = $req->send()->getBody();
    // print_r($resp);
    $jsonResponse = Zend_Json::decode($resp);
    // print_r($jsonResponse);
    if($jsonResponse['success'] == false) {
	    die('getchallenge failed:'.$jsonResponse['error']['message'].PHP_EOL);
    }

    $challengeToken = $jsonResponse['result']['token'];
    $generatedKey = md5($challengeToken.$options['userAccessKey']);
    $req->setMethod(HTTP_Request2::METHOD_POST)
        ->addPostParameter('accessKey', $generatedKey)
        ->addPostParameter('operation', 'login')
        ->addPostParameter('username', $options['userName']);
    $resp = $req->send()->getBody();
    // print_r($resp);
    $jsonResponse = Zend_Json::decode($resp);
    // print_r($jsonResponse);
    if($jsonResponse['success'] == false) {
	    die('login failed:'.$jsonResponse['error']['message'].PHP_EOL);
    }
    $sessionId = $jsonResponse['result']['sessionName']; 
    $userId = $jsonResponse['result']['userId'];

    return array('sessionId'=>$sessionId, 'userId'=>$userId);
}

function addEntry($req, $login, $data) {
    $jsondata = Zend_Json::encode($data);
    $req->setMethod(HTTP_Request2::METHOD_POST)
        ->addPostParameter('sessionName', $login['sessionId'])
        ->addPostParameter('operation', 'create')
        ->addPostParameter('elementType', 'Accounts')
        ->addPostParameter('element', $jsondata);
    $resp = $req->send()->getBody();
    // print_r($resp);
    $jsonResponse = Zend_Json::decode($resp);
    // print_r($jsonResponse);
    if($jsonResponse['success'] == false) {
	    die('addEntry failed:'.$jsonResponse['error']['message'].PHP_EOL);
    }

}


function main($options) {

    $req = new HTTP_Request2($options['endpointUrl']);
    $login = login($req, $options);

    $conn = connect_mssql($options);
    $results = get_data($conn);
    $i = 1;


    while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
        // print_r($row);
        echo('#'.$i.' '.$row['Kurzname'].' ('.$row['KontaktNummer'].')'.PHP_EOL);
        // echo "bereite auf: Strassen".PHP_EOL;
        $prefixes = array('haupt', 'rech', 'lief');
        $streetinfo = array(
            'haupt' => array('strasse' => '', 'hnr' => ''),
            'lief' => array('strasse' => '', 'hnr' => ''),
            'rech' => array('strasse' => '', 'hnr' => '')
        );
        foreach ($prefixes as $prefix) {
            preg_match_all("/([a-zA-Z\s\.\-\ß]+)\s(.*[0-9]+.*)/is", $row[$prefix.'strasse'], $parts, PREG_SET_ORDER);
            if (isset($parts[0]) && isset($parts[0][1])) {
                $streetinfo[$prefix]['strasse'] = trim($parts[0][1]);
                if (isset($parts[0][2])) {
                    $streetinfo[$prefix]['hnr'] = trim($parts[0][2]);
                }
            }
        }

        // echo "bereite auf: Verbindungen".PHP_EOL;
        $types = array('Telefon', 'email', 'Fax', 'Internet');
        $verbindungen = get_verbindungen($conn, $row['KontaktNummer']);
        $phone = '';
        $fax = '';
        $internet = '';
        $email = '';
        while ($verbindung = sqlsrv_fetch_array($verbindungen, SQLSRV_FETCH_ASSOC)) {
            switch ($verbindung['Verbindungsart']) {
                case 'Telefon':
                    $phone = $verbindung['Verbindungsnummer'];
                    break;
                case 'Fax':
                    $fax = $verbindung['Verbindungsnummer'];
                    break;
                case 'Internet':
                    $internet = $verbindung['Verbindungsnummer'];
                    break;
                case 'eMail':
                    $email = $verbindung['Verbindungsnummer'];
                    break;

            }
        }
        sqlsrv_free_stmt($verbindungen);
        $description = '';
        if (!empty($row['Bemerkungen'])) {
            $row['Bemerkungen']."\r\n\r\n";
        }
        if (!empty($row['Hinweistext'])) {
            "Hinweis: ".$row['Hinweistext'];
        }

        $data = array(
            'accountname' => $row['Kurzname'], 
            'account_no' => $row['KontaktNummer'],
            'customerno' => $row['KontaktNummer'],
            'assigned_user_id' => $login['userId'],
            'description' => $description,
            'active' => 1,
            // 'addresslevel1b' => 'addresslevel1b',
            // 'addresslevel2b' => 'addresslevel2b',
            // 'addresslevel3b' => 'addresslevel3b',
            // 'addresslevel4b' => 'addresslevel4b',
            // 'addresslevel5b' => 'addresslevel5b',
            // 'addresslevel6b' => 'addresslevel6b',
            // 'addresslevel7b' => 'addresslevel7b',
            // 'addresslevel8b' => 'addresslevel8b',
            // 'buildingnumberb' => 'buildingnumberb',
            // 'localnumberb' => 'localnumberb',
            // 'poboxb' => 'poboxb',
            'addresslevel1a' => 'Deutschland',
            //'addresslevel2a' => 'addresslevel2a',
            //'addresslevel3a' => 'addresslevel3a',
            //'addresslevel4a' => 'addresslevel4a',
            'addresslevel5a' => $row['hauptort'],
            //'addresslevel6a' => 'addresslevel6a',
            'addresslevel7a' => $row['hauptplz'],
            'addresslevel8a' => $streetinfo['haupt']['strasse'],
            'buildingnumbera' => $streetinfo['haupt']['hnr'],
            //'localnumbera' => 'localnumbera',
            // 'poboxa' => 'poboxa',
            // 'attention' => 'attention',
            'addresslevel1c' => 'Deutschland',
            // 'addresslevel2c' => 'addresslevel2c',
            // 'addresslevel3c' => 'addresslevel3c',
            // 'addresslevel4c' => 'addresslevel4c',
            'addresslevel5c' => $row['liefort'],
            // 'addresslevel6c' => 'addresslevel6c',
            'addresslevel7c' => $row['liefplz'],
            'addresslevel8c' => $streetinfo['lief']['strasse'],
            'buildingnumberc' => $streetinfo['lief']['hnr'],
            // 'localnumberc' => 'localnumberc',
            // 'poboxc' => 'poboxc',
            // 'ownership' => 'ownership',
            // 'siccode' => 'siccode',
            // 'vat_id' => 'vat_id',
            // 'registration_number_1' => 'registration_number_1',
            // 'registration_number_2' => 'registration_number_2',
            // 'legal_form' => 'legal_form',
            'phone' => $phone,
            'website' => $internet,
            'fax' => $fax,
            'email1' => $email,
            // 'verification' => 'verification',
            // 'closedtime' => 'closedtime',
            // 'products' => 'products',
            // 'services' => 'services',
            // 'crmactivity' => 'crmactivity'
        );
        // print_r($data);

        // addEntry($req, $login, $data);
        // break;
        $i++;
    }
    sqlsrv_free_stmt($results);
/*
            [accountname] => 
            [account_no] => O2
            [account_id] => 
            [industry] => 
            [accounttype] => 
            [assigned_user_id] => 19x1
            [modifiedby] => 19x1
            [shownerid] => 
            [was_read] => 0
            [active] => 0
            [addresslevel1b] => 
            [addresslevel2b] => 
            [addresslevel3b] => 
            [addresslevel4b] => 
            [addresslevel5b] => 
            [addresslevel6b] => 
            [addresslevel7b] => 
            [addresslevel8b] => 
            [buildingnumberb] => 
            [localnumberb] => 
            [poboxb] => 
            [addresslevel1a] => 
            [addresslevel2a] => 
            [addresslevel3a] => 
            [addresslevel4a] => 
            [addresslevel5a] => 
            [addresslevel6a] => 
            [addresslevel7a] => 
            [addresslevel8a] => 
            [buildingnumbera] => 
            [localnumbera] => 
            [poboxa] => 
            [description] => 
            [attention] => 
            [addresslevel1c] => 
            [addresslevel2c] => 
            [addresslevel3c] => 
            [addresslevel4c] => 
            [addresslevel5c] => 
            [addresslevel6c] => 
            [addresslevel7c] => 
            [addresslevel8c] => 
            [buildingnumberc] => 
            [localnumberc] => 
            [poboxc] => 
            [ownership] => 
            [siccode] => 
            [annual_revenue] => 0,00
            [vat_id] => 
            [registration_number_1] => 
            [registration_number_2] => 
            [legal_form] => 
            [phone] => 
            [website] => 
            [fax] => 
            [otherphone] => 
            [email1] => 
            [email2] => 
            [emailoptout] => 0
            [no_approval] => 0
            [employees] => 0
            [createdtime] => 2017-02-12 23:41:02
            [modifiedtime] => 2017-02-12 23:41:02
            [isconvertedfromlead] => 0
            [created_user_id] => 19x1
            [verification] => 
            [closedtime] => 
            [products] => 
            [services] => 
            [crmactivity] => 
            [balance] => 0,00
            [payment_balance] => 0.00000000
            [sum_time] => 0.00
            [inventorybalance] => 0.00000000
            [discount] => 0.00
            [creditlimit] => 0
            [last_invoice_date] => 
            [id] => 11x131

Administrator  Erstellt
Organisation: Christlicher Schulverein Kaiserslautern e.V.
Organisationsnummer: O7
Telefon: phone
Webseite: website
Fax: fax
Weiteres Telefon: otherphone
E-Mail: email1
Weitere E-Mail Adresse: email2
Besitzer: ownership
NACE-Code: siccode
Jahresumsatz: 0,00 €
zuständig: Administrator
Angelegt am: 13.02.2017 09:21
Beschreibung: description
Erstellt von: Administrator
MwSt: vat_id
NCR: registration_number_1
Steuernummer: registration_number_2
Land: addresslevel1a
Land: addresslevel1b
Bundesland: addresslevel2a
Bundesland: addresslevel2b
Bezirk: addresslevel3a
Bezirk: addresslevel3b
Gemeinde: addresslevel4a
Gemeinde: addresslevel4b
Ort: addresslevel5a
Ort: addresslevel5b
Stadtteil: addresslevel6a
Stadtteil: addresslevel6b
Postleitzahl: addresslevel7a
Postleitzahl: addresslevel7b
Straße: addresslevel8a
Straße: addresslevel8b
Land: addresslevel1c
Bundesland: addresslevel2c
Bezirk: addresslevel3c
Gemeinde: addresslevel4c
Ort: addresslevel5c
Stadtteil: addresslevel6c
Postleitzahl: addresslevel7c
Straße: addresslevel8c
Datenüberprüfung: verification
Hausnummer: buildingnumbera
Büronummer: localnumbera
Hausnummer: buildingnumberb
Büronummer: localnumberb
Hausnummer: buildingnumberc
Büronummer: localnumberc
Anforderungen: attention
Balance: 0,00 €
Postfach: poboxa
Postfach: poboxb
Postfach: poboxc
Zahlungsbilanz: 0
Rechtsform: legal_form
Gesamtzeit [h]: 0
Balance: 0
Rabatt: 0.00
Aktiv: Ja



*/

    // addEntry($req, $login, $data);
}

main($options);
?>
