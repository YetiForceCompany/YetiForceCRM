<?php
/**
 * Related records data from field.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * Related records data from field - class.
 */
class RelatedRecordsDataFromField extends Base
{
	/** @var string Class name */
	public $name = 'LBL_RECORDS_LIST_DATA_FROM_FIELD_TEMPLATE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = "YFParser('\$(custom : RelatedRecordsDataFromField|__SOURCE_FIELD_NAME__|__RELATION_MODULE_OR_RELATION_ID__|__FIELDS__|__CONDITIONS__|__LIMIT__|__ORDER_BY__|__RELATION_CONDITION__)\$')";

	/**
	 * Process.
	 *
	 * @return array
	 */
	public function process()
	{
		$sourceFieldName = array_shift($this->params);
		if (!$sourceFieldName || !$this->textParser->recordModel || empty($sourceRecordId = $this->textParser->recordModel->get($sourceFieldName))
			|| !\App\Record::isExists($sourceRecordId) || !($recordModel = \Vtiger_Record_Model::getInstanceById($sourceRecordId)) || !$recordModel->isViewable()
		) {
			return [];
		}

		$textParser = \App\TextParser::getInstanceByModel($recordModel)->setExtensionState($this->textParser->useExtension);
		$instance = new \App\TextParser\RelatedRecordsData($textParser, $this->params);
		return $instance->process();
	}
}
