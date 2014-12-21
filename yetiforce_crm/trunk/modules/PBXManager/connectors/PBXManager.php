<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'include/utils/utils.php';
require_once 'vtlib/Vtiger/Net/Client.php';

class PBXManager_PBXManager_Connector {

    private static $SETTINGS_REQUIRED_PARAMETERS = array('webappurl' => 'text','outboundcontext' => 'text', 'outboundtrunk' => 'text' , 'vtigersecretkey' => 'text');
    private static $RINGING_CALL_PARAMETERS = array('From' => 'callerIdNumber', 'SourceUUID' => 'callUUID', 'Direction' => 'Direction');
    private static $NUMBERS = array();
    private $webappurl;
    private $outboundcontext, $outboundtrunk;
    private $vtigersecretkey;
    const RINGING_TYPE = 'ringing';
    const ANSWERED_TYPE = 'answered';
    const HANGUP_TYPE = 'hangup';
    const RECORD_TYPE = 'record';
    
    const INCOMING_TYPE = 'inbound';
    const OUTGOING_TYPE = 'outbound';
    const USER_PHONE_FIELD = 'phone_crm_extension';

    function __construct() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $this->setServerParameters($serverModel);
    }

    /**
     * Function to get provider name
     * returns <string>
     */
    public function getGatewayName() {
        return 'PBXManager';
    }

    public function getPicklistValues($field) {
    }

    public function getServer() {
        return $this->webappurl;
    }

    public function getOutboundContext() { 
        return $this->outboundcontext; 
    } 

    public function getOutboundTrunk() { 
        return $this->outboundtrunk; 
    }
    
    public function getVtigerSecretKey() {
        return $this->vtigersecretkey;
    }

    public function getXmlResponse() {
        header("Content-type: text/xml; charset=utf-8");
        $response = '<?xml version="1.0" encoding="utf-8"?>';
        $response .= '<Response><Authentication>';
        $response .= 'Failure';
        $response .= '</Authentication></Response>';
        return $response;
    }

    /**
     * Function to set server parameters
     * @param <array>  authdetails
     */
    public function setServerParameters($serverModel) {
        $this->webappurl = $serverModel->get('webappurl');
        $this->outboundcontext = $serverModel->get('outboundcontext'); 
        $this->outboundtrunk = $serverModel->get('outboundtrunk'); 
        $this->vtigersecretkey = $serverModel->get('vtigersecretkey');
    }

    /**
     * Function to get Settings edit view params
     * returns <array>
     */
    public function getSettingsParameters() {
        return self::$SETTINGS_REQUIRED_PARAMETERS;
    }

    protected function prepareParameters($details, $type) {
        switch ($type) {
            case 'ringing':
                foreach (self::$RINGING_CALL_PARAMETERS as $key => $value) {
                    $params[$key] = $details->get($value);
                }
                $params['GateWay'] = $this->getGatewayName();
                break;
        }
        return $params;
    }

    /**
     * Function to handle the dial call event
     * @param <Vtiger_Request> $details 
     */
    public function handleDialCall($details) {
        $callid = $details->get('callUUID');

        $answeredby = $details->get('callerid2');
        $caller = $details->get('callerid1');

        // For Inbound call, answered by will be the user, we should fill the user field
        $recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($callid);
        $direction = $recordModel->get('direction');
        if ($direction == self::INCOMING_TYPE) {
            // For Incoming call, we should fill the user field if he answered that call 
            $user = PBXManager_Record_Model::getUserInfoWithNumber($answeredby);
            $params['user'] = $user['id'];
            $recordModel->updateAssignedUser($user['id']);
        } else {
            $user = PBXManager_Record_Model::getUserInfoWithNumber($caller);
            if ($user) {
                $params['user'] = $user['id'];
                $recordModel->updateAssignedUser($user['id']);
            }
        }

        $params['callstatus'] = "in-progress";
        $recordModel->updateCallDetails($params);
    }
    
    /**
     * Function to handle the EndCall event
     * @param <Vtiger_Request> $details 
     */
    public function handleEndCall($details) {
        $callid = $details->get('callUUID');
        $recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($callid);
        
        $params['starttime'] = $details->get('starttime');
        $params['endtime'] = $details->get('endtime');
        $params['totalduration'] = $details->get('duration');
        $params['billduration'] = $details->get('billableseconds');

        $recordModel->updateCallDetails($params);
    }
    
    /**
     * Function to handle the hangup call event
     * @param <Vtiger_Request> $details 
     */
    public function handleHangupCall($details) {
        $callid = $details->get('callUUID');
        $recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($callid);
        $hangupcause = $details->get('causetxt');
        
        switch ($hangupcause) {
            // If call is successfull
            case 'Normal Clearing':
                $params['callstatus'] = 'completed';
                if($details->get('HangupCause') == 'NO ANSWER') {
                    $params['callstatus'] = 'no-answer';
                }
                break;
            case 'User busy' :
                $params['callstatus'] = 'busy';
                break;
            case 'Call Rejected':
                $params['callstatus'] = 'busy';
                break;
            default :
                $params['callstatus'] = $hangupcause;
                break;
        }
        
        if($details->get('EndTime') && $details->get('Duration')) {
            $params['endtime'] = $details->get('EndTime');
            $params['totalduration'] = $details->get('Duration');
        }
        
        $recordModel->updateCallDetails($params);
    }
    
    /**
     * Function to handle record event
     * @param <Vtiger_Request> $details 
     */
    public function handleRecording($details) {
        $callid = $details->get('callUUID');
        $recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($callid);
        $params['recordingurl'] = $details->get('recordinglink');
        $recordModel->updateCallDetails($params);
    }
    
    /**
     * Function to handle AGI event
     * @param <Vtiger_Request> $details 
     */
    public function handleStartupCall($details, $userInfo, $customerInfo) {
        global $current_user;
        $params = $this->prepareParameters($details, self::RINGING_TYPE);
        $direction = $details->get('Direction');

        // To add customer and user information in params
        $params['Customer'] = $customerInfo['id'];
        $params['CustomerType'] = $customerInfo['setype'];
        $params['User'] = $userInfo['id']; 

        if ($details->get('from')) {
            $params['CustomerNumber'] = $details->get('from');
        } else if ($details->get('to')) {
            $params['CustomerNumber'] = $details->get('to');
        }
        
        $params['starttime'] = $details->get('StartTime');
        $params['callstatus'] = "ringing";
        $user = CRMEntity::getInstance('Users');
        $current_user = $user->getActiveAdminUser();
        
        $recordModel = PBXManager_Record_Model::getCleanInstance();
        $recordModel->saveRecordWithArrray($params);
        
        if ($direction == self::INCOMING_TYPE)
            $this->respondToIncomingCall($details);
        else
            $this->respondToOutgoingCall($params['CustomerNumber']);
    }
    
    /**
     * Function to respond for incoming calls
     * @param <Vtiger_Request> $details 
     */
    public function respondToIncomingCall($details) {
        global $current_user;
        self::$NUMBERS = PBXManager_Record_Model::getUserNumbers();
        
        header("Content-type: text/xml; charset=utf-8");
        $response = '<?xml version="1.0" encoding="utf-8"?>';
        $response .= '<Response><Dial><Authentication>';
        $response .= 'Success</Authentication>';

        if (self::$NUMBERS) {

            foreach (self::$NUMBERS as $userId => $number) {
                $userInstance = Users_Privileges_Model::getInstanceById($userId);
                $current_user = $userInstance;
                $callPermission = Users_Privileges_Model::isPermitted('PBXManager', 'ReceiveIncomingCalls');

                if ($number != $details->get('callerIdNumber') && $callPermission) {
                   if(preg_match("/sip/", $number) || preg_match("/@/", $number)) {
                       $number = trim($number, "/sip:/");
                       $response .= '<Number>SIP/';
                       $response .= $number;
                       $response .= '</Number>';
                   }else {
                       $response .= '<Number>SIP/';
                       $response .= $number;
                       $response .= '</Number>';
                   }
                }
            }
        }else {
            $response .= '<ConfiguredNumber>empty</ConfiguredNumber>';
            $date = date('Y/m/d H:i:s');
            $params['callstatus'] = 'no-answer';
            $params['starttime'] = $date;
            $params['endtime'] = $date;
            $recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($details->get('callUUID'));
            $recordModel->updateCallDetails($params);
        }
        $response .= '</Dial></Response>';
        echo $response;
    }
    
    /**
     * Function to respond for outgoing calls
     * @param <Vtiger_Request> $details 
     */
    public function respondToOutgoingCall($to) {
        header("Content-type: text/xml; charset=utf-8");
        $response = '<?xml version="1.0" encoding="utf-8"?>';
        $response .= '<Response><Dial><Authentication>';
        $response .= 'Success</Authentication>';
        $numberLength = strlen($to);
        
        if(preg_match("/sip/", $to) || preg_match("/@/", $to)) {
            $to = trim($to, "/sip:/");
            $response .= '<Number>SIP/';
            $response .= $to;
            $response .= '</Number>';
        }else {
            $response .= '<Number>SIP/';
            $response .= $to;
            if($numberLength > 5) $response .= '@'.  $this->getOutboundTrunk(); 
            $response .= '</Number>';
        }
        
        $response .= '</Dial></Response>';
        echo $response;
    }

    /**
     * Function to make outbound call 
     * @param <string> $number (Customer)
     * @param <string> $recordid
     */
    function call($number, $record) {
        $user = Users_Record_Model::getCurrentUserModel();
        $extension = $user->phone_crm_extension;

        $webappurl = $this->getServer();
        $context = $this->getOutboundContext(); 
        $vtigerSecretKey = $this->getVtigerSecretKey();

        $serviceURL  =  $webappurl;
        $serviceURL .= '/makecall?event=OutgoingCall&';
        $serviceURL .= 'secret=' . urlencode($vtigerSecretKey) . '&';
        $serviceURL .= 'from=' . urlencode($extension) . '&';
        $serviceURL .= 'to=' . urlencode($number) . '&';
        $serviceURL .= 'context='. urlencode($context);

        $httpClient = new Vtiger_Net_Client($serviceURL);
        $response = $httpClient->doPost(array());
        $response = trim($response); 

        if ($response == "Error" || $response == "" || $response == null
            || $response == "Authentication Failure" ) {
            return false;
        }
        return true;
    }

}