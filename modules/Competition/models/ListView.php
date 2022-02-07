<?php

/**
 * Competition list view model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Competition_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Function to get the list of Mass actions for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return array - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$links = parent::getListViewMassActions($linkParams);
		$moduleModel = $this->getModule();
		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassComposeEmail') && App\Config::main('isActiveSendingMails') && App\Mail::getDefaultSmtp()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendEmail()',
				'linkicon' => 'fas fa-envelope',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}
}
