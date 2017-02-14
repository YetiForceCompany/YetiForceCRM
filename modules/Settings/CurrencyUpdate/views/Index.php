<?php

/**
 * @package YetiForce.Views
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_CurrencyUpdate_Index_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{

		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		// synchronise bank list
		$moduleModel->refreshBanks();

		$downloadBtn = !$request->isEmpty('download') ? $request->get('download') : false;
		$date = !$request->isEmpty('duedate') ? Vtiger_Datetime_UIType::getDBInsertedValue($request->get('duedate')) : false;

		$dateCur = '';
		if ($date) {
			// if its future date change it to present one
			if (strtotime($date) > strtotime(date('Y-m-d')))
				$date = date('Y-m-d');
			$dateCur = $date;
		}
		else {
			$dateCur = date('Y-m-d');
		}

		// take currency rates for yesterday
		if (strcmp(date('Y-m-d'), $dateCur) == 0) {
			$dateCur = strtotime("-1 day", strtotime($dateCur));
			$dateCur = date('Y-m-d', $dateCur);
		}

		$dateCur = vtlib\Functions::getLastWorkingDay($dateCur);

		// get currency if not already archived
		if ($downloadBtn) {
			$moduleModel->fetchCurrencyRates($dateCur);
		}

		$selectBankId = $moduleModel->getActiveBankId();

		$history = $moduleModel->getRatesHistory($selectBankId, $dateCur, $request);
		$bankTab = array();

		$db = new \App\Db\Query();
		$db->from('yetiforce_currencyupdate_banks');
		$dataReader = $db->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$bankTab[$i]['id'] = $row['id'];
			$bankName = $row['bank_name'];
			$bankTab[$i]['bank_name'] = $bankName;
			$bankTab[$i]['active'] = $row['active'];
			$i++;
		}

		// number of currencies
		$curr_num = $moduleModel->getCurrencyNum();
		// get info about main currency
		$mainCurrencyInfo = vtlib\Functions::getDefaultCurrencyInfo();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULENAME', 'CurrencyUpdate');
		$viewer->assign('DATE', ($request->has('duedate') ? (new Vtiger_Date_UIType())->getDisplayValue($dateCur) : ''));
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
