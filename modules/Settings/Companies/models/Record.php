<?php

/**
 * Companies record model class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Record_Model extends Settings_Vtiger_Record_Model
{

	public static $logoSupportedFormats = ['jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png'];
	public $logoPath = 'storage/Logo/';

	/**
	 * Function to get the Id
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		return '?module=Companies&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url
	 * @return string URL
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Companies&parent=Settings&action=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=Companies&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of companies record model
	 * @param int $id
	 * @return \self instance, if exists.
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
	 * Function to save
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
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $key
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
			case 'logo_login':
			case 'logo_main':
			case 'logo_mail':
				$value = "<img src='{$this->getLogoPath($value)}' class='alignMiddle'/>";
				break;
		}
		return $value;
	}

	/**
	 * Function to get the Display Value, for the checbox field type with given DB Insert Value
	 * @param int $value
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
	 * Function to delete the current Record Model
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
	 * Function to get the list view actions for the record
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
				'linkicon' => 'glyphicon glyphicon-pencil btn btn-xs btn-success',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => $this->getDeleteActionUrl(),
				'linkicon' => 'glyphicon glyphicon-trash btn btn-xs btn-danger',
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get Logo path to display
	 * @param string $name logo name
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
	 * Function to save the logoinfo
	 */
	public function saveLogo($name)
	{
		$uploadDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $this->logoPath;
		$logoName = $uploadDir . \App\Fields\File::sanitizeUploadFileName($_FILES[$name]['name']);
		move_uploaded_file($_FILES[$name]['tmp_name'], $logoName);
		copy($logoName, $uploadDir . 'application.ico');
	}

	/**
	 * Function to update company info
	 * @param Vtiger_Request $request
	 * @return array
	 */
	public static function updateCompany(Vtiger_Request $request)
	{
		$data = $request->get('param');
		$recordId = $data['record'];
		$duplicateName = false;
		if ($recordId) {
			$recordModel = self::getInstance($recordId);
		} else {
			$recordModel = new self();
			$duplicateName = (new \App\Db\Query())->from('s_#__companies')
				->where(['name' => $data['name']])
				->orWhere(['short_name' => $data['short_name']])
				->exists();
		}
		if (!$duplicateName) {
			if ('on' === $data['default']) {
				App\Db::getInstance('admin')->createCommand()->update('s_#__companies', ['default' => 0])->execute();
			}
			$columns = Settings_Companies_Module_Model::getColumnNames();
			if ($columns) {
				$status = false;
				$images = ['logo_login', 'logo_main', 'logo_mail'];
				foreach ($images as $image) {
					$saveLogo[$image] = $status = true;
					if (!empty($_FILES[$image]['name'])) {
						$logoDetails[$image] = $_FILES[$image];
						$fileInstance = \App\Fields\File::loadFromRequest($logoDetails[$image]);
						if (!$fileInstance->validate('image')) {
							$saveLogo[$image] = false;
						}
						//mime type check
						if ($fileInstance->getShortMimeType(0) !== 'image' || !in_array($fileInstance->getShortMimeType(1), self::$logoSupportedFormats)) {
							$saveLogo[$image] = false;
						}
						if ($saveLogo[$image]) {
							$recordModel->saveLogo($image);
						}
					} else {
						$saveLogo[$image] = true;
					}
				}
				foreach ($columns as $fieldName) {
					$fieldValue = $data[$fieldName];
					if ($fieldName === 'logo_login' || $fieldName === 'logo_main' || $fieldName === 'logo_mail') {
						if (!empty($logoDetails[$fieldName]['name'])) {
							$fieldValue = ltrim(basename(" " . \App\Fields\File::sanitizeUploadFileName($logoDetails[$fieldName]['name'])));
						} else {
							$fieldValue = $recordModel->get($fieldName);
						}
					}
					if ('default' === $fieldName) {
						$fieldValue = $data['default'] === 'on' ? 1 : 0;
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				$recordModel->save();
			}
			$return = ['success' => true, 'url' => $recordModel->getDetailViewUrl()];
		} else {
			$return = ['success' => false, 'message' => \App\Language::translate('LBL_COMPANY_NAMES_EXIST')];
		}
		return $return;
	}
}
