<?php

/**
 * Companies record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_Record_Model extends Settings_Vtiger_Record_Model
{
	public static $logoPath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'storage/CompaniesLogo';

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
			case 'type':
				$value = $this->getDisplayTypeValue((int) $value);
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
			case 'logo':
				$src = \App\Fields\File::getImageBaseData($this->getLogoPath());
				$value = "<img src='$src' class='img-thumbnail'/>";
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Get the displayed value for the type column.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayTypeValue(int $value): string
	{
		switch ($value) {
			case 1:
				$label = 'LBL_TYPE_TARGET_USER';
				break;
			case 2:
				$label = 'LBL_TYPE_INTEGRATOR';
				break;
			case 3:
			default:
				$label = 'LBL_TYPE_PROVIDER';
				break;
		}
		return \App\Language::translate($label, 'Settings::Companies');
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
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-xs btn-danger',
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get Logo path to display.
	 *
	 * @return string path
	 */
	public function getLogoPath()
	{
		$logo = static::$logoPath . $this->getId();
		if (file_exists($logo)) {
			return $logo;
		}
		return '';
	}

	/**
	 * Function to save company logos.
	 *
	 * @return array
	 */
	public function saveCompanyLogos()
	{
		if (!empty($_FILES['logo']['name'])) {
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['logo']);
			if ($fileInstance->validate('image')) {
				$path = static::$logoPath . $this->getId();
				if (file_exists($path)) {
					unlink($path);
				}
				$fileInstance->moveFile($path);
			}
		}
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
			->where(['name' => $request->get('name')]);
		if ($request->get('record')) {
			$query->andWhere(['<>', 'id', $request->get('record')]);
		}
		return $query->exists($db);
	}
}
