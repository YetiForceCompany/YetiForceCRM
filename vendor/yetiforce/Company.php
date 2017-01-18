<?php
namespace App;

/**
 * Company basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Company extends Base
{

	/** @var Logo directory */
	public static $logoPath = 'storage/Logo/';

	/**
	 * Function to get the instance of the Company model
	 * @param int $id
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
	 * Function to get the Company Logo
	 * @return \Vtiger_Image_Model instance
	 */
	public function getLogo($type = false)
	{
		if (Cache::has('CompanyLogo', $type)) {
			return Cache::get('CompanyLogo', $type);
		}
		$logoName = decode_html($this->get($type ? $type : 'logo_main'));
		if (!$logoName) {
			return false;
		}
		$logoModel = new \Vtiger_Image_Model();
		$logoModel->setData([
			'imageUrl' => \AppConfig::main('site_URL') . static::$logoPath . $logoName,
			'imagePath' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, static::$logoPath) . $logoName,
			'alt' => $logoName,
			'imageName' => $logoName,
			'title' => Language::translate('LBL_COMPANY_LOGO_TITLE'),
		]);
		Cache::save('CompanyLogo', $type, $logoModel);
		return $logoModel;
	}
}
