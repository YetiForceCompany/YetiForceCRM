<?php
/**
 * Logo class to handle files.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Logo class to handle files.
 */
class MultiCompany_Logo_File extends Vtiger_Basic_File
{
	/** {@inheritdoc} */
	public $storageName = 'MultiCompany';

	/** {@inheritdoc} */
	public function getCheckPermission(App\Request $request)
	{
		if (!App\Session::has('authenticated_user_id') || $request->isEmpty('record', true) || $request->isEmpty('key', true)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	/**
	 * Get company logo.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function get(App\Request $request)
	{
		$userModel = \App\User::getUserModel($request->getInteger('record'));
		$logo = $userModel->get('multiCompanyLogo');
		if (!$logo || ($logo['key'] !== $request->getByType('key', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$file = \App\Fields\File::loadFromInfo([
			'path' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $logo['path'],
			'name' => $logo['name'],
		]);
		header('pragma: public');
		header('cache-control: max-age=86400, public');
		header('expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
		header('content-type: ' . $file->getMimeType());
		header('content-transfer-encoding: binary');
		header('content-length: ' . $file->getSize());
		readfile($file->getPath());
	}
}
