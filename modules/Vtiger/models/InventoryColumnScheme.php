<?php

/**
 * Inventory Column Scheme Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Vtiger_InventoryColumnScheme_Model extends Vtiger_Record_Model
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public static $baseTable = 'a_yf_pdf_inv_col_scheme';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	public static $baseIndex = 'id';

	/**
	 * Records cache.
	 *
	 * @var array
	 */
	protected $recordCache = [];

	/**
	 * Current record id.
	 *
	 * @var int
	 */
	protected $recordId;

	/**
	 * View to picklist assigment array.
	 *
	 * @var array
	 */
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];

	/**
	 * Function to get the id of the record.
	 *
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 *  Return key value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		if ($key === 'conditions' && !is_array(parent::get($key))) {
			return json_decode(parent::get($key), true);
		}
		return parent::get($key);
	}

	/**
	 * Return raw key value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getRaw($key)
	{
		return parent::get($key);
	}

	/**
	 * Get record id for which template is generated.
	 *
	 * @return <Integer> - id of a main module record
	 */
	public function getMainRecordId()
	{
		return $this->crmid;
	}

	/**
	 * Sets record id for which template will be generated.
	 *
	 * @param <Integer> $id
	 */
	public function setMainRecordId($id)
	{
		$this->crmid = $id;
	}

	/**
	 * Return module instance or false.
	 *
	 * @return false|object
	 */
	public function getModule()
	{
		return Vtiger_Module_Model::getInstance($this->get('module_name'));
	}

	/**
	 * Get instance by id.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return bool|Vtiger_PDF_Model
	 */
	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		$pdf = Vtiger_Cache::get('InventoryColumnSchemeModel', $recordId);
		if ($pdf) {
			return $pdf;
		}
		$row = (new \App\Db\Query())->from(self::$baseTable)->where([self::$baseIndex => $recordId])->one();
		if ($row === false) {
			return false;
		}
		if ($moduleName == 'Vtiger' && isset($row['module_name'])) {
			$moduleName = $row['module_name'];
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'InventoryColumnScheme', $moduleName);
		$pdf = new $handlerClass();
		$pdf->setData($row);
		Vtiger_Cache::set('InventoryColumnSchemeModel', $recordId, $pdf);
		return $pdf;
	}

	/**
	 * Get instance by id.
	 *
	 * @param int $recordId
	 *
	 * @return bool|Vtiger_PDF_Model
	 */
	public static function getInstanceByTargetId($recordId)
	{
		$pdf = Vtiger_Cache::get('InventoryColumnSchemeModelByTarget', $recordId);
		if ($pdf) {
			return $pdf;
		}
		$row = (new \App\Db\Query())->from(self::$baseTable)->where(['crmid' => $recordId])->one();
		$instance = new self();
		if ($row) {
			$instance->setRawData($row);
		}
		$instance->set('crmid', $recordId);
		Vtiger_Cache::set('InventoryColumnSchemeModelByTarget', $recordId, $instance);
		return $instance;
	}

	/**
	 * Check if user has permissions to record.
	 *
	 * @return bool
	 */
	public function checkUserPermissions()
	{
		$permissions = $this->get('template_members');
		if (empty($permissions)) {
			return true;
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$permissions = explode(',', $permissions);
		$getTypes = [];
		foreach ($permissions as $name) {
			$valueType = explode(':', $name);
			$getTypes[$valueType[0]][] = $valueType[1];
		}
		if (in_array('Users:' . $currentUser->getId(), $permissions)) { // check user id
			return true;
		}
		if (in_array('Roles:' . $currentUser->getRole(), $permissions)) {
			return true;
		}
		if (array_key_exists('Groups', $getTypes)) {
			$accessibleGroups = array_keys(\App\Fields\Owner::getInstance($this->get('module_name'), $currentUser)->getAccessibleGroupForModule());
			$groups = array_intersect($getTypes['Groups'], $currentUser->getGroups());
			if (array_intersect($groups, $accessibleGroups)) {
				return true;
			}
		}
		if (array_key_exists('RoleAndSubordinates', $getTypes)) {
			$roles = $currentUser->getParentRoles();
			$roles[] = $currentUser->getRole();
			if (array_intersect($getTypes['RoleAndSubordinates'], array_filter($roles))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get column scheme.
	 *
	 * @return array column list
	 */
	public function getColumnScheme()
	{
		$scheme = null;
		$record = (new App\Db\Query())
			->select(['id', 'crmid', 'columns'])
			->from('a_#_pdf_inv_col_scheme')
			->where(['id' => $this->getId()])
			->createCommand()
			->queryOne();
		if ($record) {
			$scheme = json_decode($record['columns']);
		}
		return $scheme;
	}
}
