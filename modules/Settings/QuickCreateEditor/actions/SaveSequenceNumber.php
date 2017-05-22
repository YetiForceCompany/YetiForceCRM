<?php

/**
 * Settings QuickCreateEditor SaveSequenceNumber action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_QuickCreateEditor_SaveSequenceNumber_Action extends Settings_Vtiger_Index_Action
{

	public function __construct()
	{
		$this->exposeMethod('move');
	}

	public function move(\App\Request $request)
	{
		$updatedFieldsList = $request->get('updatedFields');

		//This will update the fields sequence for the updated blocks
		Settings_QuickCreateEditor_Module_Model::updateFieldSequenceNumber($updatedFieldsList);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
}
