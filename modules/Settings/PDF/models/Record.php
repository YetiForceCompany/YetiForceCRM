<?php

/**
 * Record Class for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_PDF_Record_Model extends Settings_Vtiger_Record_Model
{
	protected $recordCache = [];
	protected $fieldsCache = [];
	protected $moduleRecordId;

	/**
	 * Function to get the id of the record.
	 *
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
	 * Function to get the list view actions for the record.
	 *
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
				'linkicon' => 'fas fa-edit',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=PDF&parent=Settings&action=ExportTemplate&id=' . $this->getId(),
				'linkicon' => 'fas fa-upload',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => '#',
				'linkicon' => 'fas fa-trash-alt',
			],
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
		$db = \App\Db::getInstance('admin');

		switch ($step) {
			case 2:
			case 3:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				$fields = [];
				foreach ($stepFields as $field) {
					if ($field === 'conditions') {
						$params = json_encode($pdfModel->get($field));
					} else {
						$params = $pdfModel->get($field);
					}
					$fields[$field] = $params;
				}

				$db->createCommand()
					->update('a_#__pdf', $fields, ['pdfid' => $pdfModel->getId()])
					->execute();
				return $pdfModel->get('pdfid');
			case 1:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				if (!$pdfModel->getId()) {
					$params = [];
					foreach ($stepFields as $field) {
						$params[$field] = $pdfModel->get($field);
					}
					$db->createCommand()->insert('a_#__pdf', $params)->execute();
					$pdfModel->set('pdfid', $db->getLastInsertID('a_#__pdf_pdfid_seq'));
				} else {
					$fields = [];
					foreach ($stepFields as $field) {
						$fields[$field] = $pdfModel->get($field);
					}
					$db->createCommand()->update('a_#__pdf', $fields, ['pdfid' => $pdfModel->getId()])
						->execute();
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
				$db->createCommand()->insert('a_#__pdf', $params)->execute();
				$pdfModel->set('pdfid', $db->getLastInsertID('a_#__pdf_pdfid_seq'));

				return $pdfModel->get('pdfid');
			default:
				break;
		}
	}

	/**
	 * Delete watermark.
	 *
	 * @param Vtiger_PDF_Model $pdfModel
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return bool
	 */
	public static function deleteWatermark(Vtiger_PDF_Model $pdfModel)
	{
		$db = \App\Db::getInstance('admin');
		$watermarkImage = $pdfModel->get('watermark_image');
		$db->createCommand()
			->update('a_#__pdf', ['watermark_image' => ''], ['pdfid' => $pdfModel->getId()])
			->execute();
		if (file_exists($watermarkImage)) {
			return unlink($watermarkImage);
		}
		return false;
	}

	public static function delete(Vtiger_PDF_Model $pdfModel)
	{
		return App\Db::getInstance('admin')->createCommand()
			->delete('a_#__pdf', ['pdfid' => $pdfModel->getId()])
			->execute();
	}

	/**
	 * Function transforms Advance filter to workflow conditions.
	 */
	public static function transformAdvanceFilterToWorkFlowFilter(Vtiger_PDF_Model &$pdfModel)
	{
		$conditions = $pdfModel->get('conditions');
		$wfCondition = [];
		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = ['fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0', ];
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = ['fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'] ?? '', 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid'], ];
					}
				}
			}
		}
		$pdfModel->set('conditions', $wfCondition);
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
			case 'status':
				$value = $value ? 'PLL_ACTIVE' : 'PLL_INACTIVE';
				break;
			case 'margin_chkbox':
				$value = $value ? 'LBL_YES' : 'LBL_NO';
				break;
			default:
				break;
		}
		return $value;
	}
}
