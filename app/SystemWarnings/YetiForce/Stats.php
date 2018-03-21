<?php

namespace App\SystemWarnings\YetiForce;

/**
 * Privilege File basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		if (file_exists('cache/' . $this->getKey()) || \AppConfig::main('systemMode') === 'demo') {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}

	/**
	 * Get unique key.
	 *
	 * @return type
	 */
	public function getKey()
	{
		return sha1('Stats' . \AppConfig::main('site_URL') . \App\Version::get());
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
		try {
			$request = \Requests::POST('https://api.yetiforce.com/stats', [], array_merge($params, [
					'key' => sha1(\AppConfig::main('site_URL') . ROOT_DIRECTORY),
					'version' => \App\Version::get(),
					'language' => \App\Language::getLanguage(),
					'timezone' => date_default_timezone_get(),
					]), ['useragent' => 'YetiForceCRM']);
			if ($request->body === 'OK') {
				file_put_contents('cache/' . $this->getKey(), 'Stats');
				$result = true;
				$message = \App\Language::translate('LBL_DATA_SAVE_OK', 'Settings::SystemWarnings');
			}
		} catch (\Exception $exc) {
			\App\Log::warning($exc->getMessage());
		}

		return ['result' => $result, 'message' => $message];
	}
}
