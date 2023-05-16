<?php

/**
 * Mail signature module model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Mail signature module model class.
 */
class Settings_MailSignature_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'MailSignature';
	/** {@inheritdoc} */
	public $baseTable = 's_#__mail_signature';
	/** {@inheritdoc} */
	public $baseIndex = 'id';
	/** {@inheritdoc} */
	public $listFields = ['name' => 'FL_NAME', 'status' => 'FL_ACTIVE', 'default' => 'FL_DEFAULT'];
	/** @var string[] Fields name for edit view */
	public $editFields = ['name', 'status', 'default', 'body'];

	/**
	 * Function to get the url for Create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for edit view of the module.
	 *
	 * @return string - url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=List';
	}

	/**
	 * Function verifies if it is possible to sort by given field in list view.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function isSortByName($fieldName)
	{
		return \in_array($fieldName, ['name', 'status', 'imap_host']);
	}

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fields = $this->listFields;
			$fieldObjects = [];
			foreach ($fields as $fieldName => $fieldLabel) {
				$fieldObject = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
				if (!$this->isSortByName($fieldName)) {
					$fieldObject->set('sort', true);
				}
				$fieldObjects[$fieldName] = $fieldObject;
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Editable fields.
	 *
	 * @return array
	 */
	public function getEditableFields(): array
	{
		return $this->editFields;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_AutomaticAssignment_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getEditViewStructure($recordModel = null): array
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($recordModel && $recordModel->has($fieldName)) {
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			} else {
				$defaultValue = $fieldModel->get('defaultvalue');
				$fieldModel->set('fieldvalue', $defaultValue ?? '');
			}
			$block = $fieldModel->get('blockLabel') ?: '';
			$structure[$block][$fieldName] = $fieldModel;
		}

		return $structure;
	}

	/**
	 * Get block icon.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getBlockIcon($name): string
	{
		return '';
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$params = [];
		switch ($name) {
			case 'name':
				$params = [
					'name' => $name,
					'label' => 'FL_NAME',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'status':
				$params = [
					'name' => $name,
					'label' => 'FL_ACTIVE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'default':
				$params = [
					'name' => $name,
					'label' => 'FL_DEFAULT',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'body':
				$params = [
					'name' => $name,
					'label' => 'FL_BODY',
					'uitype' => 300,
					'typeofdata' => 'V~M',
					'maximumlength' => 16777215,
					'purifyType' => \App\Purifier::HTML,
					'blockLabel' => 'FL_BODY',
					'col-md' => 'col-md-12',
					'table' => $this->getBaseTable()
				];
				break;
			default:
				break;
		}
		return $params ? \Vtiger_Field_Model::init($this->getName(true), $params, $name) : null;
	}
}
