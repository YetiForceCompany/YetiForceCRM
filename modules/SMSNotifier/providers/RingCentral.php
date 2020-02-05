<?php

/**
 * RINGCENTRAL - sms provider.
 */
class SMSNotifier_RingCentral_Provider extends SMSNotifier_Basic_Provider
{
    /**
     * Provider name.
     *
     * @var string
     */
    protected $name = 'RingCentral';

    /**
     * Address URL.
     *
     * @var string
     */
    protected $url = 'https://platform.ringcentral.com';

    /**
     * Encoding.
     *
     * @var string
     */
    public $encoding = 'utf-8';

    /**
     * Format.
     *
     * @var string
     */
    public $format = 'json';

    /**
     * Required fields.
     *
     * @return string[]
     */
    public function getRequiredParams()
    {
        return ['CLIENT_ID','RINGUSER','RINGPASS','RINGPHONE','RINGEXT'];
    }

    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Response.
     *
     * @param Requests_Response $request
     *
     * @return bool
     */
    public function getResponse(Requests_Response $request)
    {
        $response = \App\Json::decode($request->body);

        return isset($response['error']) && !empty($response['error']) ? false : true;
    }

    /**
     * Fields to edit in settings.
     *
     * @return \Settings_Vtiger_Field_Model[]
     */
    public function getSettingsEditFieldsModel()
    {
        $fields = [];
        $moduleName = 'Settings:SMSNotifier';
        foreach ($this->getRequiredParams() as $name) {
            $field = ['uitype' => 1, 'column' => $name, 'name' => $name, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
            switch ($name){
                case ("CLIENT_ID" || "RINGUSER" || "RINGPASS" || "RINGPHONE" || "RINGEXT"):
                    $field['text'] = [''];
                    $field['label'] = $name;
                    $fields[] = $field;
                    break;
            }
        }
        foreach ($fields as &$field) {
            $field = Settings_Vtiger_Field_Model::init($moduleName, $field);
        }
        return $fields;
    }

    public function getPatch()
    {
        $keys = $this->getRequiredParams();
        $keys[] = $this->toName;
        $keys[] = $this->messageName;
        $params = [];
        foreach ($keys as $key) {
            $params[$key] = $this->get($key);
        }

        return $params;

    }

    public function send() {

        $url = $this->getUrl();
        $cliend_secret = $this->getAuthorization();
        $patch = $this->getPatch();
//
        $login =  new RingCentral\SDK\SDK($patch['CLIENT_ID'], $cliend_secret, $url);
        $platform = $login->platform();
        $RINGCENTRAL_USERNAME = $patch['RINGUSER'];
        $RINGCENTRAL_NUMBER = $patch['RINGPHONE'];
        $RINGCENTRAL_PASSWORD = $patch['RINGPASS'];
        $RINGCENTRAL_EXTENSION = $patch['RINGEXT'];
        try {
            $platform->login($RINGCENTRAL_USERNAME,
                $RINGCENTRAL_EXTENSION,
                $RINGCENTRAL_PASSWORD);
            $params = array(
                'from' => array('phoneNumber' => $RINGCENTRAL_NUMBER),
                'to' => array(
                    array('phoneNumber' => $patch['to']),
                ),
                'text' => $patch['message'],
            );
            $r = $platform->post('/account/~/extension/~/sms', $params);
        } catch (\RingCentral\SDK\Http\ApiException $e) {
            print 'Expected HTTP Error: ' . $e->getMessage() . PHP_EOL;
        }

        return true;
    }
}
