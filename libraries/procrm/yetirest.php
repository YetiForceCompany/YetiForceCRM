<?php

/**
 * class for accessing Yetiforce REST interface
 * @license YetiForce Public License 1.1
 * @author Alex Weber <migoi.snow@gmail.com>
 * @version 1.0
 */
class YetiRest {
    /** @var string The host of Yetiforce */
    protected $host;
    /** @var string The name of the webservice */
    protected $wsname;
    /** @var string The password of the webservice */
    protected $wspass;
    /** @var string The webservice token */
    protected $wstoken;
    /** @var string The session token (after user loggen in) */
    protected $token;
    /** @var string url path for REST webservice */
    protected $baseurl;
    /** @var int The user id returned after login */
    // public $userId; // did not make it with PR to YetiForceCRM
    /** @var boolean debug output of curl communication */
    public $debug;

    /**
     * Constructor
     *
     * @param string  $host     The host of Yetiforce
     * @param string  $wsname   The name of the webservice
     * @param string  $wspass   The password of the webservice
     * @param string  $wstoken  The webservice token
     */
    public function __construct($host, $wsname, $wspass, $wstoken) {
        $this->host = trim((string) $host, '/');
        $this->wsname = (string) $wsname;
        $this->wspass = (string) $wspass;
        $this->wstoken = (string) $wstoken;
        $this->token = '';
        $this->baseurl = 'api/webservice';
        // $this->userId = false; // did not make it with PR to YetiForceCRM
        $this->debug = false;
    }

    /**
     * Issue request to Yetiforce
     *
     * @param string $type          HTTP method for the request
     * @param string $module        The HTTP affected module for the request
     * @param string $action        The api action for the request
     * @param array  $data          The payload to send with the request
     * @param array  $extraheaders  The extraheaders to use
     *
     * @return array Array with response
     */
    protected function request($type, $module='', $action='', $data=array(), $extraheaders=array()) {
        $url = $this->host.'/'.$this->baseurl;
        if (isset($module) && $module !== '') {
          $url = $url.'/'.$module;
        }
        if (isset($action) && $action !== '') {
          $url = $url.'/'.$action;
        }
        $request = curl_init();
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_USERPWD, $this->wsname.':'.$this->wspass);

        $headers = array( 
            'Content-type: application/json', 
            'Accept: application/json',
            'USER-AGENT : yeti-rest-api/1.0',
            'X-ENCRYPTED: 0',
            'X-API-KEY: '.$this->wstoken,
        ); 
        if ($this->token !== '') {
          $headers[] = 'X-Token: '.$this->token;
        }
        $headers = array_merge($headers, $extraheaders);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $jsondata = json_encode($data);
        if ($type === 'POST') {
          curl_setopt($request, CURLOPT_POST, 1);
          curl_setopt($request, CURLOPT_POSTFIELDS, $jsondata);
        }
        if ($type === 'PUT') {
          curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
          curl_setopt($request, CURLOPT_POSTFIELDS, $jsondata);
        }

        $jsonresponse = false;
        if ($this->debug) {
          curl_setopt($request, CURLOPT_VERBOSE, true);
        }
        $response = curl_exec($request);
        $err      = curl_errno($request); 
        if ($err > 0) {
          $errmsg   = curl_error($request) ; 
          echo('Error ('.$err.'): '.$errmsg).PHP_EOL;
        } else {
          $jsonresponse = json_decode($response, true);
        }
        if ($jsonresponse['status'] !== 1) {
          echo('REST Error ('.$jsonresponse['error']['code'].'): '.$jsonresponse['error']['message']).PHP_EOL;
        }
        curl_close($request);
        return $jsonresponse;
    }

    /**
     * Login user to Yetiforce REST
     *
     * @param string $username The username for login
     * @param string $password The password for login
     *
     * @return boolean success
     */
    public function login($username, $password) {
        $type = 'POST';
        $module = 'Users';
        $action = 'Login';
        $data = array(
          'userName' => $username,
          'password' => $password,
          'params' => array()
        );

        $response = $this->request($type, $module, $action, $data);
        if ($response && $response['status'] == 1){
          $this->token = $response['result']['token'];
          // $this->userId = $response['result']['id']; // did not make it with PR to YetiForceCRM
          return true;
        }
        return false;
    }

    /**
     * Logout user from Yetiforce REST
     *
     * @return boolean success
     */
    public function logout() {
        $type = 'PUT'; 
        $module = 'Users';
        $action = 'Logout';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          $this->token = '';
          return true;
        }
        return false;
    }

    /**
     * List modules of Yetiforce REST
     *
     * @return array modules
     */
    public function listModules() {
        $type = 'GET';
        $module = '';
        $action = 'Modules';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * List methods of Yetiforce REST
     *
     * @return array methods
     */
    public function listMethods() {
        $type = 'GET';
        $module = '';
        $action = 'Methods';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * List records for module
     *
     * @param string $module  The module to get records from
     * @param int    $limit   Max rows to return
     * @param int    $offset  offset to start from
     * @param array  $fields  array of fieldnames to return
     *
     * @return array data
     */
    public function listRecords($module, $limit = 1000, $offset=0, $fields=false) {
        $type = 'GET';
        $module = (string) $module;
        $action = 'RecordsList';
        $data = array();

        $extraheaders = array();
        if (is_array($fields)) {
          $extraheaders[] = 'X-FIELDS: '.json_encode($fields);;
        }
        if ($limit) {
          $extraheaders[] = 'X-ROW-LIMIT: '.$limit;
        }
        if ($offset) {
          $extraheaders[] = 'X-ROW-OFFSET: '.$offset;
        }
        $response = $this->request($type, $module, $action, $data, $extraheaders);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * privileges for module
     *
     * @param string $module  The module to get records from
     *
     * @return array data
     */
    public function privileges($module) {
        $type = 'GET';
        $module = (string) $module;
        $action = 'Privileges';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * hierarchy for module
     *
     * @param string $module  The module to get records from
     *
     * @return array data
     */
    public function hierarchy($module) {
        $type = 'GET';
        $module = (string) $module;
        $action = 'Hierarchy';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * fields for module
     *
     * @param string $module  The module to get records from
     *
     * @return array data
     */
    public function fields($module) {
        $type = 'GET';
        $module = (string) $module;
        $action = 'Fields';

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result']['fields'];
        }
        return array();
    }

    /**
     * get record with id for module
     *
     * @param string $module  The module to get record from
     * @param string $id      The id of the record
     *
     * @return array data
     */
    public function getRecord($module, $id) {
        $type = 'GET';
        $module = (string) $module;
        $action = 'Record/'.$id;

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * delete record with id for module
     *
     * @param string $module  The module to get record from
     * @param string $id      The id of the record
     *
     * @return array data
     */
    public function deleteRecord($module, $id) {
        $type = 'DELETE';
        $module = (string) $module;
        $action = 'Record/'.$id;

        $response = $this->request($type, $module, $action);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * save record with id for module (update)
     *
     * @param string $module  The module to get record from
     * @param string $id      The id of the record
     * @param array  $record  The data of the record
     *
     * @return array data
     */
    public function updateRecord($module, $id, $record) {
        $type = 'PUT';
        $module = (string) $module;
        $action = 'Record/'.$id;
        $record['module'] = $module;
        $record['action'] = 'Save';
        $record['record'] = $id;
        $data = $record;
        $response = $this->request($type, $module, $action, $data);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }

    /**
     * create new record for module
     *
     * @param string $module  The module to get record from
     * @param array  $record  The data of the record
     *
     * @return array new record id
     */
    public function createRecord($module, $record) {
        $type = 'POST';
        $module = (string) $module;
        $action = 'Record/';
        $record['module'] = $module;
        $record['action'] = 'Save';
        $data = $record;
        $response = $this->request($type, $module, $action, $data);
        if ($response && $response['status'] == 1) {
          return $response['result'];
        }
        return array();
    }
}

?>