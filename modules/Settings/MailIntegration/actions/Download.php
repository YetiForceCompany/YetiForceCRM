<?php
/**
 * MailIntegration module model class.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MailIntegration_Download_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
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
		$ik = \App\YetiForce\Register::getInstanceKey();
		$manifestPath = ROOT_DIRECTORY . '/modules/MailIntegration/installation/outlook.xml';
		$body = file_get_contents($manifestPath);
		$body = str_replace([
			'{__CRM_URL__}',
			'{__CRM_GUID__}',
			'{__ACCESS_TOKEN__}',
		], [
			Config\Main::$site_URL,
			vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($ik, 4)),
			substr($ik, 0, 30),
		], $body);

		header('content-type: text/xml');
		header('content-disposition: attachment; filename="outlook_manifest.xml";');
		header('content-length: ' . \strlen($body));
		echo $body;
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
