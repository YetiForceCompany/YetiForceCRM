<?php

class Settings_GlobalPermission_SaveField_Action extends Settings_Vtiger_Save_Action {

    public function checkPermission(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if (!$currentUser->isAdminUser()) {
            throw new \Exception\AppException('LBL_PERMISSION_DENIED');
        }
    }

    public function process(Vtiger_Request $request)
    {
        $fieldValues = $request->get('field-access-user');
        foreach($fieldValues as $fieldId => $values) {
            $instance = Vtiger_Field_Model::getInstance($fieldId);
            $instance->wipeAccessRestrictionsForUsers();
            $instance->allowUsers($values);
        }


        $fieldValues = $request->get('field-access-role');
        foreach($fieldValues as $fieldId => $values) {
            $instance = Vtiger_Field_Model::getInstance($fieldId);
            $instance->wipeAccessRestrictionsForRoles();
            $instance->allowRoles($values);
        }
        header('location:index.php');
    }
}