<?php
/**
 * Update currency rates.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Settings_CurrencyUpdate_CurrencyUpdate_Cron class.
 */
class Settings_CurrencyUpdate_CurrencyUpdate_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
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
	}
}
