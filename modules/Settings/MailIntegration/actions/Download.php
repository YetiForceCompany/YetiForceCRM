<?php
/**
 * MailIntegration module model class.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MailIntegration_Download_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('outlook');
	}

	/**
	 * Generate outlook manifest.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function outlook(App\Request $request)
	{
		$manifestPath = ROOT_DIRECTORY . '/modules/MailIntegration/installation/outlook.xml';
		$body = file_get_contents($manifestPath);
		$body = str_replace([
			'{__CRM_URL__}',
			'{__CRM_GUID__}'
		], [
			Config\Main::$site_URL,
			vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(\App\YetiForce\Register::getInstanceKey(), 4))
		], $body);

		header('content-type: text/xml');
		header('content-disposition: attachment; filename="outlook_manifest.xml";');
		header('content-length: ' . mb_strlen($body));
		echo $body;
	}
}
