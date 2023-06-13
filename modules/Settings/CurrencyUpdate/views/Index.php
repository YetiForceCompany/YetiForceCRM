<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();

		// synchronise bank list
		$moduleModel->refreshBanks();

		$downloadBtn = !$request->isEmpty('download') ? $request->getByType('download') : false;
		if (!$request->isEmpty('duedate')) {
			$dateStart = \App\Fields\Date::formatToDB($request->getByType('duedate', 'DateInUserFormat'));
			if (strtotime($dateStart) > strtotime(date('Y-m-d'))) {
				$dateStart = date('Y-m-d');
			}
			$dateEnd = $dateStart;
		} else {
			$dateStart = date('Y-m-d'); //date('Y-m-01');
			$dateEnd = date('Y-m-t');
		}

		// take currency rates for yesterday
		if (0 == strcmp(date('Y-m-d'), $dateStart)) {
			$dateStart = date('Y-m-d', strtotime('-1 day', strtotime($dateStart)));
		}
		$dateStart = vtlib\Functions::getLastWorkingDay($dateStart);

		// get currency if not already archived
		if ($downloadBtn) {
			$moduleModel->fetchCurrencyRates($dateStart);
		}

		$selectBankId = $moduleModel->getActiveBankId();

		$history = $moduleModel->getRatesHistory($selectBankId, $request->isEmpty('duedate') ? date('Y-m-01') : $dateStart, $dateEnd);
		$bankTab = [];

		$db = new \App\Db\Query();
		$db->from('yetiforce_currencyupdate_banks');
		$dataReader = $db->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$bankTab[$i]['id'] = $row['id'];
			$bankName = $row['bank_name'];
			$bankTab[$i]['bank_name'] = $bankName;
			$bankTab[$i]['active'] = $row['active'];
			++$i;
		}
		$dataReader->close();
		// number of currencies
		$curr_num = $moduleModel->getCurrencyNum();
		// get info about main currency
		$mainCurrencyInfo = \App\Fields\Currency::getDefault();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULENAME', 'CurrencyUpdate');
		$viewer->assign('DATE', ($request->has('duedate') ? (new Vtiger_Date_UIType())->getDisplayValue($dateStart) : ''));
		$viewer->assign('CURRNUM', $curr_num);
		$viewer->assign('BANK', $bankTab);
		$viewer->assign('HISTORIA', $history);
		$viewer->assign('MAINCURR', $mainCurrencyInfo);
		$viewer->assign('SUPPORTED_CURRENCIES', $moduleModel->getSupportedCurrencies());
		$viewer->assign('UNSUPPORTED_CURRENCIES', $moduleModel->getUnSupportedCurrencies());
		$viewer->view('Index.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}
}
