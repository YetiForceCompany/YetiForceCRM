<?php

/**
 * Related attachments.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * Related attachments class.
 */
class RelatedAttachments extends Base
{
	/** @var string Class name */
	public $name = 'LBL_RELATED_ATTACHMENTS';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : RelatedAttachments|__FIELD_NAMES__|__CONDITIONS__|__ATTACH_FILES__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$relatedModuleName = 'Documents';
		if (!$this->textParser->recordModel
			|| !\App\Privilege::isPermitted($relatedModuleName)
			|| !($relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relatedModuleName))
		) {
			return '';
		}

		[$fields, $conditions, $attachFiles] = array_pad($this->params, 3, '');
		$pdf = $attachFiles ? $this->textParser->getParam('pdf') : null;
		if (trim($conditions)) {
			$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition(\App\Json::decode($conditions));
			$relationListView->set('search_params', $transformedSearchParams);
		}
		$fields = array_filter(explode(',', trim($fields)));
		foreach ($fields as $key => $field) {
			if (!($fieldModel = $relationListView->getRelatedModuleModel()->getFieldByName($field)) || !$fieldModel->isActiveField()) {
				unset($fields[$key]);
			}
		}
		$fields = $fields ?: ['notes_title', 'filename'];
		$relationListView->setFields(array_unique(array_merge($fields, ['notes_title', 'filename', 'filelocationtype'])));
		$rows = [];
		$counter = 0;
		foreach ($relationListView->getAllEntries() as $relatedRecordModel) {
			++$counter;
			$row = [];
			foreach ($fields as $fieldName) {
				$value = $relatedRecordModel->getDisplayValue($fieldName, false, true);
				$value = trim($value);
				$row[] = 'filename' === $fieldName ? "({$value})" : $value;
			}
			$rows[] = "{$counter}. " . implode(', ', $row);
			if ($pdf && $relatedRecordModel->checkFileIntegrity()
			  && ($info = $relatedRecordModel->getFileDetails())
			  && ($filePath = $info['path'] . $info['attachmentsid'])
			  && !isset($pdf->attachFiles[$filePath])
			) {
				$pdf->attachFiles[$filePath] = ['name' => $info['name'], 'path' => $filePath];
			}
		}
		return empty($rows) ? '' : implode('<br>', $rows);
	}
}
