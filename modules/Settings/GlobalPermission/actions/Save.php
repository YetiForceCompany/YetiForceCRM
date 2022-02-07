<?php

/**
 * Settings GlobalPermission save action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_GlobalPermission_Save_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::setRecordId(\App\Request::_getInteger('profileID'));
		parent::__construct();
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$profileID = $request->getInteger('profileID');
		$checked = $request->getBoolean('checked');
		$globalactionid = $request->getInteger('globalactionid');
		if (1 === $globalactionid) {
			$globalActionName = 'LBL_VIEW_ALL';
		} else {
			$globalActionName = 'LBL_EDIT_ALL';
		}
		if ($checked) {
			$prev[$globalActionName] = 1;
		} else {
			$prev[$globalActionName] = 0;
		}
		$checked = (int) (!$checked);
		$post[$globalActionName] = $checked;
		Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);
		Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SAVE_OK', $request->getModule(false))]);
		$response->emit();
	}
}
