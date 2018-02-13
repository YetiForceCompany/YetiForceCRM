<?php

/**
 * Settings GlobalPermission save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_GlobalPermission_Save_Action extends Settings_Vtiger_Save_Action
{
    public function __construct()
    {
        Settings_Vtiger_Tracker_Model::setRecordId(\App\Request::_get('profileID'));
        parent::__construct();
    }

    public function process(\App\Request $request)
    {
        $profileID = $request->get('profileID');
        $checked = $request->get('checked');
        $globalactionid = $request->get('globalactionid');
        if ($globalactionid == 1) {
            $globalActionName = 'LBL_VIEW_ALL';
        } else {
            $globalActionName = 'LBL_EDIT_ALL';
        }
        if ($checked == 'true') {
            $checked = 1;
            $prev[$globalActionName] = 0;
        } else {
            $checked = 0;
            $prev[$globalActionName] = 1;
        }
        $post[$globalActionName] = $checked;
        Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);
        Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_OK', $request->getModule(false))]);
        $response->emit();
    }
}
