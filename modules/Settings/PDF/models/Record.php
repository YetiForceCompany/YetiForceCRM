<?php

/**
 * Record Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Record_Model extends Settings_Vtiger_Record_Model
{

	protected $recordCache = [];
	protected $fieldsCache = [];
	protected $moduleRecordId;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('pdfid');
	}

	public function getName()
	{
		return $this->get('primary_name');
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

	public static function getCleanInstance($moduleName = 'Vtiger')
	{
		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdf = new $handlerClass();
		$data = [];
		$fields = Settings_PDF_Module_Model::getFieldsByStep();
		foreach ($fields as $field) {
			$data[$field] = '';
		}
		$pdf->setData($data);
		return $pdf;
	}

	public static function save(Vtiger_PDF_Model $pdfModel, $step = 1)
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
						$params = json_encode($pdfModel->get($field));
					} else {
						$params = $pdfModel->get($field);
					}
					$fields[$field] = $params;
				}
				$db->update('a_yf_pdf', $fields, '`pdfid` = ? LIMIT 1', [$pdfModel->getId()]);
				return $pdfModel->get('pdfid');

			case 1:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				if (!$pdfModel->getId()) {
					$params = [];
					foreach ($stepFields as $field) {
						$params[$field] = $pdfModel->get($field);
					}
					$db->insert('a_yf_pdf', $params);

					$pdfModel->set('pdfid', $db->getLastInsertID());
				} else {
					$fields = [];
					foreach ($stepFields as $field) {
						$fields[$field] = $pdfModel->get($field);
					}
					$db->update('a_yf_pdf', $fields, '`pdfid` = ? LIMIT 1', [$pdfModel->getId()]);
				}
				return $pdfModel->get('pdfid');

			case 'import':
				$allFields = Settings_PDF_Module_Model::$allFields;
				$params = [];
				foreach ($allFields as $field) {
					if ($field === 'conditions') {
						$params[$field] = json_encode($pdfModel->get($field));
					} else {
						$params[$field] = $pdfModel->get($field);
					}
				}
				$db->insert('a_yf_pdf', $params);

				$pdfModel->set('pdfid', $db->getLastInsertID());
				return $pdfModel->get('pdfid');
		}
	}

	public static function deleteWatermark(Vtiger_PDF_Model $pdfModel)
	{
		$db = PearDatabase::getInstance();
		$watermarkImage = $pdfModel->get('watermark_image');

		$query = 'UPDATE `a_yf_pdf` SET `watermark_image` = ? WHERE `pdfid` = ? LIMIT 1;';
		$db->pquery($query, ['', $pdfModel->getId()]);

		if (file_exists($watermarkImage)) {
			return unlink($watermarkImage);
		}
		return false;
	}

	public static function delete(Vtiger_PDF_Model $pdfModel)
	{
		$db = PearDatabase::getInstance();
		return $db->delete('a_yf_pdf', '`pdfid` = ?', [$pdfModel->getId()]);
	}

	/**
	 * Function transforms Advance filter to workflow conditions
	 */
	public static function transformAdvanceFilterToWorkFlowFilter(Vtiger_PDF_Model &$pdfModel)
	{
		$conditions = $pdfModel->get('conditions');
		$wfCondition = [];
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
		$pdfModel->set('conditions', $wfCondition);
	}
}
