<?php

/**
 * PaymentsIn step1 view  class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class PaymentsIn_step1_View extends Vtiger_Index_View
{

	public function preProcess(\App\Request $request)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$moduleSettingsName = $request->getModule(false);
		$moduleName = $request->getModule();
		$paymentsIn = [];
		$record = Vtiger_Record_Model::getCleanInstance($moduleName);
		$type = $request->get('type');
		$bank = $request->get('bank');
		$fileInstance = \App\Fields\File::loadFromRequest($_FILES['file']);
		if (!$fileInstance->validate()) {
			return false;
		}
		$this->saveFile();
		$recordParse = $record->getSummary($type, $bank, $_FILES['file']['name']);

		// only incomming records (C)
		$i = 0;
		$j = [];
		foreach ($recordParse->operations as $transfers) {
			foreach ($transfers as $key => $value) {
				if ($key == 'indicator' && $value == 'C')
					$paymentsIn[] = $transfers;
				if ($key == 'third_letter_currency_code') {
					$j[] = $i;
				}
			}
			$i++;
		}

		$json = json_encode($paymentsIn);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('PAYMENTSIN', $paymentsIn);
		$viewer->assign('COUNT', count($paymentsIn));
		$viewer->assign('JSON', $json);
		echo $viewer->view('step1.tpl', $moduleSettingsName, true);
	}

	public function saveFile()
	{
		$address = vglobal('cache_dir');
		$localisation = $address . $_FILES['file']['name'];
		if (is_uploaded_file($_FILES['file']['tmp_name'])) {
			if (!move_uploaded_file($_FILES['file']['tmp_name'], $localisation)) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}
}
