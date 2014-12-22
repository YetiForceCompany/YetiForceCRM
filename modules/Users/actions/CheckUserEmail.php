<?php

class Users_CheckUserEmail_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        
        $moduleModel = Vtiger_Module_Model::getInstance('Users');
        $output = !$moduleModel->checkMailExist($request->get('email'), $request->get('cUser'));
        
        $response = new Vtiger_Response();
        $response->setResult($output);
        $response->emit();
    }

}
