<?php

/**
 * Update sequence of countries
 * @package YetiForce.Webservice
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Brüggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_UpdateSequence_Action extends Settings_Vtiger_Index_Action
{

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$sequencesList = $request->get('sequencesList');

		$moduleModel = Settings_Countries_Module_Model::getInstance($qualifiedModuleName);

		$response = new Vtiger_Response();
		if ($sequencesList) {
			$moduleModel->updateSequence($sequencesList);
			$response->setResult([true]);
		} else {
			$response->setError();
		}

		$response->emit();
	}
}
