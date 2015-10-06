<?php

/**
 * Record Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Record_Model extends Settings_Vtiger_Record_Model
{

	public function getId()
	{
		return $this->get('pdfid');
	}

	public function getName()
	{
		return $this->get('summary');
	}

	public function get($key)
	{
		if ($key === 'conditions' && !is_array(parent::get($key))) {
			return json_decode(parent::get($key), true);
		} else {
			return parent::get($key);
		}
	}

	public function getRaw($key)
	{
		return parent::get($key);
	}

	public function getEditViewUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Edit&record=' . $this->getId();
	}

	public function getModule()
	{
		return $this->module;
	}

	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=PDF&parent=Settings&action=ExportTemplate&id=' . $this->getId(),
				'linkicon' => 'glyphicon glyphicon-export'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => '#',
				'linkicon' => 'glyphicon glyphicon-trash'
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	public static function getInstanceById($recordId)
	{
		$db = PearDatabase::getInstance();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:PDF');

		$query = 'SELECT `' . $moduleModel->baseIndex . '`,`' . implode('`,`', Settings_PDF_Module_Model::$allFields) . '` FROM `' . $moduleModel->baseTable . '` WHERE `' . $moduleModel->baseIndex . '` = ? LIMIT 1;';
		$result = $db->pquery($query, [$recordId]);

		if ($db->num_rows($result) == 0) {
			return false;
		}

		$row = $db->fetchByAssoc($result);

		$pdf = new self;
		$pdf->setData($row);

		return $pdf;
	}

	public static function getCleanInstance()
	{
		$pdf = new self;
		$data = [];
		$fields = Settings_PDF_Module_Model::getFieldsByStep();
		foreach ($fields as $field) {
			$data[$field] = '';
		}
		$pdf->setData($data);
		return $pdf;
	}

	public function save($step = 1)
	{
		$db = PearDatabase::getInstance();

		switch ($step) {
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				$params = [];
				$fields = [];
				foreach ($stepFields as $field) {
					if ($field === 'conditions') {
						$params[] = json_encode($this->get($field));
					} else {
						$params[] = $this->get($field);
					}
					$fields[] = "`$field` = ?";
				}

				$params[] = $this->getId();

				$query = 'UPDATE `a_yf_pdf` SET ' . implode(',', $fields) . ' WHERE `pdfid` = ? LIMIT 1;';
				$result = $db->pquery($query, $params);
				return $this->get('pdfid');

			case 1:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				if (!$this->getId()) {
					$params = [];
					foreach ($stepFields as $field) {
						$params[$field] = $this->get($field);
					}
					$db->insert('a_yf_pdf', $params);

					$this->set('pdfid', $db->getLastInsertID());
				} else {
					$params = [];
					$fields = [];
					foreach ($stepFields as $field) {
						$params[] = $this->get($field);
						$fields[] = "`$field` = ?";
					}

					$params[] = $this->getId();
					$query = 'UPDATE `a_yf_pdf` SET ' . implode(',', $fields) . ' WHERE `pdfid` = ? LIMIT 1;';
					$result = $db->pquery($query, $params);
				}
				return $this->get('pdfid');

			case 'import':
				$allFields = Settings_PDF_Module_Model::$allFields;
				$params = [];
				foreach ($allFields as $field) {
					if ($field === 'conditions') {
						$params[$field] = json_encode($this->get($field));
					} else {
						$params[$field] = $this->get($field);
					}
				}
				$db->insert('a_yf_pdf', $params);

				$this->set('pdfid', $db->getLastInsertID());
				return $this->get('pdfid');
		}
	}

	public function import()
	{
		$this->save('import');
	}

	public function delete()
	{
		$db = PearDatabase::getInstance();
		return $db->delete('a_yf_pdf', '`pdfid` = ?', [$this->getId()]);
	}

	/**
	 * Function returns valuetype of the field filter
	 * @return <String>
	 */
	function getFieldFilterValueType($fieldname)
	{
		$conditions = $this->get('conditions');
		if (!empty($conditions) && is_array($conditions)) {
			foreach ($conditions as $filter) {
				if ($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	/**
	 * Function transforms Advance filter to workflow conditions
	 */
	function transformAdvanceFilterToWorkFlowFilter()
	{
		$conditions = $this->get('conditions');
		$wfCondition = array();

		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0');
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = array('fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid']);
					}
				}
			}
		}
		$this->set('conditions', $wfCondition);
	}

	function transformToAdvancedFilterCondition($conditions = false)
	{
		if (!$conditions) {
			$conditions = $this->get('conditions');
		}
		$transformedConditions = array();

		if (!empty($conditions)) {
			foreach ($conditions as $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				} else {
					$secondGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				}
			}
		}
		$transformedConditions[1] = array('columns' => $firstGroup);
		$transformedConditions[2] = array('columns' => $secondGroup);
		return $transformedConditions;
	}

	function deleteWatermark()
	{
		$db = PearDatabase::getInstance();
		$watermarkImage = $this->get('watermark_image');

		$query = 'UPDATE `a_yf_pdf` SET `watermark_image` = ? WHERE `pdfid` = ? LIMIT 1;';
		$db->pquery($query, ['', $this->getId()]);

		if (file_exists($watermarkImage)) {
			return unlink($watermarkImage);
		}

		return false;
	}

	public function deleteConditions()
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `a_yf_pdf` SET `conditions` = "" WHERE `pdfid` = ? LIMIT 1;';
		$db->pquery($query, [$this->getId()]);
	}

	public function checkFiltersForRecord($recordId)
	{
		require_once("modules/com_vtiger_workflow/VTJsonCondition.inc");
		require_once("modules/com_vtiger_workflow/VTEntityCache.inc");

		$conditionStrategy = new VTJsonCondition();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$entityCache = new VTEntityCache($currentUser);
		$wsId = vtws_getWebserviceEntityId($this->get('module_name'), $recordId);

		return $conditionStrategy->evaluate($this->getRaw('conditions'), $entityCache, $wsId);;
	}

	public function checkUserPermissions($userId, $userGroups)
	{
		$permissions = $this->get('template_members');

		if (empty($permissions)) {
			return true;
		}

		$permissions = explode(',', $this->get('template_members'));

		if (in_array('Users:' . $userId, $permissions)) { // check user id
			return true;
		} else {
			foreach ($userGroups as $group) {
				if (in_array('Groups:' . $group, $permissions)) {
					return true;
				}
			}
		}

		return false;
	}
}
