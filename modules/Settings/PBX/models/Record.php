<?php

/**
 * Record Model.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Edit fields.
	 *
	 * @var array
	 */
	private $editFields = [
		'name' => ['label' => 'LBL_NAME'],
		'type' => ['label' => 'LBL_TYPE', 'uitype' => 16],
		'default' => ['label' => 'LBL_DEFAULT', 'uitype' => 56, 'typeofdata' => 'C~O'],
	];

	/**
	 * Connector configuration.
	 *
	 * @var array
	 */
	public $param;

	/**
	 * Record ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('pbxid');
	}

	/**
	 * Record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the confog param for a given key.
	 *
	 * @param string $key
	 *
	 * @return mixed Value for the given key
	 */
	public function getParam($key)
	{
		return isset($this->param[$key]) ? $this->param[$key] : null;
	}

	/**
	 * Function to get Module instance.
	 *
	 * @return Settings_PBX_Module_Model
	 */
	public function getModule()
	{
		if (!$this->module) {
			$this->module = Settings_Vtiger_Module_Model::getInstance('Settings:PBX');
		}
		return $this->module;
	}

	/**
	 * Function to set Module instance.
	 *
	 * @param Settings_PBX_Module_Model $moduleModel
	 *
	 * @return $this
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;

		return $this;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $name
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		if ('default' === $key) {
			return $this->get($key) ? \App\Language::translate('LBL_YES') : \App\Language::translate('LBL_NO');
		}
		return $this->get($key);
	}

	/**
	 * Function determines fields available in edition view.
	 *
	 * @return string[]
	 */
	public function getEditFields()
	{
		return $this->editFields;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => 'index.php?module=PBX&parent=Settings&view=EditModal&record=' . $this->getId(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-primary',
				'modalView' => true,
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Settings_Vtiger_List_Js.deleteById(' . $this->getId() . ');',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get the instance, given id.
	 *
	 * @param int    $id
	 * @param string $type
	 *
	 * @return \self
	 */
	public static function getInstanceById($id)
	{
		if (\App\Cache::staticHas('Settings_PBX_Record_Model', $id)) {
			return \App\Cache::staticGet('Settings_PBX_Record_Model', $id);
		}
		$instance = self::getCleanInstance();
		$data = (new App\Db\Query())
			->from('s_#__pbx')
			->where(['pbxid' => $id])
			->one();
		if (!empty($data['param'])) {
			$instance->param = \App\Json::decode($data['param']);
		}
		$instance->setData($data);
		\App\Cache::staticSave('Settings_PBX_Record_Model', $id, $instance);

		return $instance;
	}

	/**
	 * Function to get the clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$instance = new self();
		$instance->module = Settings_Vtiger_Module_Model::getInstance('Settings:PBX');

		return $instance;
	}

	/**
	 * Function removes record.
	 *
	 * @return bool
	 */
	public function delete()
	{
		$recordId = $this->getId();
		if ($recordId) {
			$db = App\Db::getInstance();
			$result = $db->createCommand()->delete('s_#__pbx', ['pbxid' => $recordId])->execute();
		}
		return !empty($result);
	}

	/**
	 * Get edit fields model.
	 *
	 * @return Settings_Vtiger_Field_Model[]
	 */
	public function getEditFieldsModel()
	{
		$moduleName = $this->getModule()->getName(true);
		$mainParams = ['uitype' => 1, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		$fieldModels = [];
		foreach ($this->editFields as $name => $params) {
			if ('type' === $name) {
				$connectors = [];
				foreach (App\Integrations\Pbx::getConnectors() as $connectorName => $instance) {
					$connectors[$connectorName] = \App\Language::translate($instance->name, $moduleName);
				}
				$params['picklistValues'] = $connectors;
			}
			$fieldModel = Settings_Vtiger_Field_Model::init($moduleName, array_merge($mainParams, $params, ['column' => $name, 'name' => $name]));
			$fieldModel->set('fieldvalue', $this->get($name));
			if ('type' === $name && $this->getId()) {
				$fieldModel->set('isEditableReadOnly', true);
			}
			$fieldModels[$name] = $fieldModel;
		}
		return $fieldModels;
	}

	/**
	 * Get connector config fields model.
	 *
	 * @return type
	 */
	public function getConnectorFieldsModel()
	{
		$moduleName = $this->getModule()->getName(true);
		$mainParams = ['uitype' => 1, 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => 0, 'isEditableReadOnly' => false];
		$fieldModels = [];
		$connector = App\Integrations\Pbx::getConnectorInstance($this->get('type'));
		if ($connector) {
			foreach ($connector->configFields as $name => $params) {
				$fieldModel = Settings_Vtiger_Field_Model::init($moduleName, array_merge($mainParams, $params, ['column' => $name, 'name' => $name]));
				$fieldModel->set('fieldvalue', $this->getParam($name));
				$fieldModels[$name] = $fieldModel;
			}
		}
		return $fieldModels;
	}

	/**
	 * Save pbx instance.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$data = $this->getData();
		if ($data['default']) {
			$db->createCommand()->update('s_#__pbx', ['default' => 0])->execute();
		}
		if ($this->getId()) {
			unset($data['pbxid']);
			$seccess = true;
			$db->createCommand()->update('s_#__pbx', $data, ['pbxid' => $this->getId()])->execute();
		} else {
			$seccess = $db->createCommand()->insert('s_#__pbx', $data)->execute();
			if ($seccess) {
				$this->set('pbxid', $db->getLastInsertID('s_#__pbx_pbxid_seq'));
			}
		}
		return $seccess;
	}

	/**
	 * Parse dana from request.
	 *
	 * @param array $data
	 */
	public function parseFromRequest($data)
	{
		foreach ($this->getEditFields() as $name => $value) {
			$this->set($name, $data[$name] ?? null);
		}
		$connector = App\Integrations\Pbx::getConnectorInstance($data['type']);
		$params = [];
		foreach ($connector->configFields as $name => $config) {
			$params[$name] = $data[$name] ?? null;
		}
		$this->param = $params;
		$this->set('param', \App\Json::encode($params));
	}
}
