<?php
/**
 * 
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
require_once('include/main/WebUI.php');
\App\Log::trace('Start CRON:' . __FILE__);

$moduleModel = Settings_CurrencyUpdate_Module_Model::getCleanInstance();
$activeBankId = $moduleModel->getActiveBankId();
if (!empty($activeBankId)) {
	$yesterday = date('Y-m-d', strtotime('-1 day'));
	$lastWorkingDay = vtlib\Functions::getLastWorkingDay($yesterday);
	$status = $moduleModel->fetchCurrencyRates($lastWorkingDay, true);

	if ($status) {
		\App\Log::trace('Successfully fetched new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
	} else {
		\App\Log::warning('Failed to fetch new currency exchange rates for date: ' . $lastWorkingDay . ' from bank: ' . $moduleModel->getActiveBankName());
	}
} else {
	\App\Log::warning('Update of system currency rates ignored, no active bankin settings.');
}
\App\Log::trace('End CRON:' . __FILE__);
