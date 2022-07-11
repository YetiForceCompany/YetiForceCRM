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
	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('PAID_RECORD_COLLECTOR', ['Gus', 'PLCeidg', 'PLNationalCourtRegister', 'PLVatPayerStatusVerification', 'UKCompaniesHouse', 'UKVatPayerStatusVerification', 'USAEdgarRegistryFromSec', 'OrbIntelligence']);
		$viewer->assign('SHOP_RECORD_COLLECTOR', ['YetiForcePlCeidg', 'YetiForcePlKrs', 'YetiForcePlVatPayerStatus', 'YetiForceOrb', 'YetiForceUkCompaniesHouse', 'YetiForceUkVatPayerStatus', 'YetiForceUsaEdgar']);
		$viewer->assign('COLLECTORS', Settings_RecordCollector_Module_Model::getInstance('Settings:RecordCollector')->getCollectors());
		$viewer->view('List.tpl', $request->getModule(false));
	}
}
