<?php

namespace App\SystemWarnings\YetiForce;

/**
 * Privilege File basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Stats extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_STATS';
	protected $priority = 8;
	protected $tpl = true;

	/**
	 * Checking whether all the configuration parameters are correct.
	 */
	public function process()
	{
		if (\App\YetiForce\Register::verify(true) || \AppConfig::main('systemMode') === 'demo') {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}

	/**
	 * Update ignoring status.
	 *
	 * @param int $params
	 *
	 * @return bool
	 */
	public function update($params)
	{
		if (gethostbyname('yetiforce.com') === 'yetiforce.com') {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION');

			return 'ERR_NO_INTERNET_CONNECTION';
		}
		$result = false;
		$message = \App\Language::translate('LBL_DATA_SAVE_FAIL', 'Settings::SystemWarnings');
		$register = new \App\YetiForce\Register();
		if ($register->send()) {
			$result = true;
			$message = \App\Language::translate('LBL_DATA_SAVE_OK', 'Settings::SystemWarnings');
		} elseif (isset($register->error)) {
			$message .= \PHP_EOL . \App\Language::translate($register->error, 'Other::Exceptions');
		}
		return ['result' => $result, 'message' => $message];
	}
}
