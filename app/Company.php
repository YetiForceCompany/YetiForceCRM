<?php

namespace App;

/**
 * Company basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Company extends Base
{
	/**
	 * Logo directory.
	 *
	 * @var string
	 */
	public static $logoPath = 'public_html/layouts/resources/Logo/';

	/**
	 * Function to get the instance of the Company model.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getInstanceById($id = false)
	{
		if (Cache::has('CompanyDetail', $id)) {
			return Cache::get('CompanyDetail', $id);
		}
		if ($id) {
			$row = (new \App\Db\Query())->from('s_#__companies')->where(['id' => $id])->one();
		} else {
			$row = (new \App\Db\Query())->from('s_#__companies')->where(['default' => 1])->one();
		}
		$self = new self();
		if ($row) {
			$self->setData($row);
		}
		Cache::save('CompanyDetail', $id, $self, Cache::LONG);

		return $self;
	}

	/**
	 * Function to get the Company Logo.
	 *
	 * @return \Vtiger_Image_Model instance
	 */
	public function getLogo($type = false, $fullUrl = false)
	{
		if (Cache::has('CompanyLogo', $type)) {
			return Cache::get('CompanyLogo', $type);
		}
		$logoName = Purifier::decodeHtml($this->get($type ? $type : 'logo_main'));
		if (!$logoName) {
			return false;
		}
		$logoURL = static::$logoPath . $logoName;
		if (IS_PUBLIC_DIR) {
			$logoURL = str_replace('public_html/', '', $logoURL);
		}
		if ($fullUrl) {
			$logoURL = \AppConfig::main('site_URL') . $logoURL;
		}
		$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, static::$logoPath) . $logoName;
		$logoModel = new \Vtiger_Image_Model();
		$logoModel->setData([
			'imageUrl' => $logoURL,
			'imagePath' => $path,
			'alt' => $logoName,
			'imageName' => $logoName,
			'title' => Language::translate('LBL_COMPANY_LOGO_TITLE'),
			'fileExists' => file_exists($path),
		]);
		Cache::save('CompanyLogo', $type, $logoModel);

		return $logoModel;
	}
}
