<?php
namespace App\SystemWarnings\YetiForce;

/**
 * Privilege File basic class
 * @package YetiForce.SystemWarnings
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Newsletter extends \App\SystemWarnings\Template
{

	protected $title = 'LBL_NEWSLETTER';
	protected $priority = 8;
	protected $tpl = true;

	/**
	 * Checking whether all the configuration parameters are correct
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
	 * Get unique key
	 * @return type
	 */
	public function getKey()
	{
		return sha1('Newsletter' . \AppConfig::main('site_URL') . ROOT_DIRECTORY);
	}

	/**
	 * Update ignoring status
	 * @param int $params
	 * @return boolean
	 */
	public function update($params)
	{
		if (gethostbyname('yetiforce.com') === 'yetiforce.com') {
			\App\Log::warning('ERR_NO_INTERNET_CONNECTION');
			return 'ERR_NO_INTERNET_CONNECTION';
		}
		$return = false;
		try {
			$request = \Requests::POST('https://api.yetiforce.com/newsletter', [], array_merge($params, [
					'key' => sha1(\AppConfig::main('site_URL') . ROOT_DIRECTORY),
					'version' => \App\Version::get(),
					'language' => \Vtiger_Language_Handler::getLanguage(),
					'timezone' => date_default_timezone_get(),
					]), ['useragent' => 'YetiForceCRM']);
			if ($request->body === 'OK') {
				file_put_contents('cache/' . $this->getKey(), 'Newsletter');
				$return = true;
			}
		} catch (\Exception $exc) {
			\App\Log::warning($exc->getMessage());
		}
		return $return;
	}
}
