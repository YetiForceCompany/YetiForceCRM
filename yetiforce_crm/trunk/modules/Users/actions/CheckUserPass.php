<?php

class Users_CheckUserPass_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        
        $response = new Vtiger_Response();
        $response->setResult(Settings_Password_Record_Model::checkPassword($request->get('pass')));
        $response->emit();
    }

}
