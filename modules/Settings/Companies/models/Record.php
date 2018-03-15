<?php

/**
 * Companies record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Record_Model extends Settings_Vtiger_Record_Model
{
	public static $logoNames = ['logo_login', 'logo_main', 'logo_mail'];
	public static $logoSupportedFormats = ['jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png'];
	public $logoPath = 'public_html/layouts/resources/Logo/';

	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		return '?module=Companies&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=Companies&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of companies record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstance($id)
	{
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__companies')->where(['id' => $id])->one($db);
		$instance = false;
		if ($row) {
			$instance = new self();
			$instance->setData($row);
		}

		return $instance;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();
		$params = $this->getData();
		if ($recordId) {
			$db->createCommand()->update('s_#__companies', $params, ['id' => $recordId])->execute();
		} else {
			$db->createCommand()->insert('s_#__companies', $params)->execute();
			$this->set('id', $db->getLastInsertID('s_#__companies_id_seq'));
		}
		\App\Cache::clear();
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue($key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'default':
				$value = $this->getDisplayCheckboxValue($value);
				break;
			case 'tabid':
				$value = \App\Module::getModuleName($value);
				break;
			case 'industry':
				$value = App\Language::translate($value);
				break;
			case 'country':
				$value = \App\Language::translateSingleMod($value, 'Other.Country');
				break;
			case 'logo_login':
			case 'logo_main':
			case 'logo_mail':
				$src = \App\Fields\File::getImageBaseData($this->getLogoPath($value));
				$value = "<img src='$src' class='alignMiddle'/>";
				break;
		}

		return $value;
	}

	/**
	 * Function to get the Display Value, for the checbox field type with given DB Insert Value.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayCheckboxValue($value)
	{
		if (0 === $value) {
			$value = \App\Language::translate('LBL_NO');
		} else {
			$value = \App\Language::translate('LBL_YES');
		}

		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()
			->delete('s_#__companies', ['id' => $this->getId()])
			->execute();
		\App\Cache::clear();
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn btn-xs btn-info',
			],
		];
		if (0 === $this->get('default')) {
			$recordLinks[] = [
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-xs btn-danger',
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get Logo path to display.
	 *
	 * @param string $name logo name
	 *
	 * @return string path
	 */
	public function getLogoPath($name)
	{
		$logoPath = $this->logoPath;
		if (!is_dir($logoPath)) {
			return '';
		}
		$iterator = new \DirectoryIterator($logoPath);
		foreach ($iterator as $fileInfo) {
			if ($name === $fileInfo->getFilename() && in_array($fileInfo->getExtension(), self::$logoSupportedFormats) && !$fileInfo->isDot() && !$fileInfo->isDir()) {
				return $logoPath . $name;
			}
		}

		return '';
	}

	/**
	 * Function to save the logoinfo.
	 */
	public function saveLogo($name)
	{
		$uploadDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $this->logoPath;
		$logoName = $uploadDir . \App\Fields\File::sanitizeUploadFileName($_FILES[$name]['name']);
		move_uploaded_file($_FILES[$name]['tmp_name'], $logoName);
		copy($logoName, $uploadDir . 'application.ico');
	}

	/**
	 * Function to check if company duplicated.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function isCompanyDuplicated(\App\Request $request)
	{
		$db = App\Db::getInstance('admin');
		$query = new \App\Db\Query();
		$query->from('s_#__companies')
			->where(['name' => $request->get('name')])
			->orWhere(['short_name' => $request->get('short_name')]);
		if ($request->get('record')) {
			$query->andWhere(['<>', 'id', $request->get('record')]);
		}

		return $query->exists($db);
	}

	/**
	 * Function to set companies not default.
	 *
	 * @param string $name
	 */
	public function setCompaniesNotDefault($default)
	{
		if ($default) {
			App\Db::getInstance('admin')->createCommand()->update('s_#__companies', ['default' => 0])->execute();
		}
	}

	/**
	 * Function to save company logos.
	 *
	 * @return array
	 */
	public function saveCompanyLogos()
	{
		$logoDetails = [];
		foreach (self::$logoNames as $image) {
			$saveLogo[$image] = true;
			if (!empty($_FILES[$image]['name'])) {
				$logoDetails[$image] = $_FILES[$image];
				$fileInstance = \App\Fields\File::loadFromRequest($logoDetails[$image]);
				if (!$fileInstance->validate('image')) {
					$saveLogo[$image] = false;
				}
				if ($fileInstance->getShortMimeType(0) !== 'image' || !in_array($fileInstance->getShortMimeType(1), self::$logoSupportedFormats)) {
					$saveLogo[$image] = false;
				}
				if ($saveLogo[$image]) {
					$this->saveLogo($image);
				}
			}
		}

		return $logoDetails;
	}
}
