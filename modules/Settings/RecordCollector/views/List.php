<?php

/**
 * Settings RecordCollector List view file.
 *
 * @package Settings.Views
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 */

/**
 * Settings RecordCollector List view class.
 */
class Settings_RecordCollector_List_View extends Settings_Vtiger_Index_View
{
	private $paidCollectorsNames = [
		'Gus',
		'PLNationalCourtRegister',
		'PLVatPayerStatusVerification',
		'UKCompaniesHouse'
	];

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $request->getModule(false));
		$viewer->assign('PAIDCOLLECTORS', $this->paidCollectorsNames);
		$viewer->assign('COLLECTORS', Settings_RecordCollector_Module_Model::getInstance('Settings:RecordCollector')->getCollectors());
		$viewer->view('List.tpl', $request->getModule(false));
	}
}
