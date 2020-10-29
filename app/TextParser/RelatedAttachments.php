<?php

/**
 * Related attachments.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public $default = '$(custom : RelatedAttachments|__CONDITIONS__|__ATTACH_FILES__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$relatedModuleName = 'Documents';
		if (!$this->textParser->recordModel ||
			!\App\Privilege::isPermitted($relatedModuleName) ||
			!($relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relatedModuleName))
		) {
			return '';
		}

		[$conditions, $attachFiles] = array_pad($this->params, 2, '');
		$pagingModel = new \Vtiger_Paging_Model();
		if ($conditions) {
			$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition(\App\Json::decode($conditions));
			$relationListView->set('search_params', $transformedSearchParams);
		}
		$relationListView->setFields(['notes_title', 'filename', 'filelocationtype']);
		$rows = [];
		$counter = 0;
		foreach ($relationListView->getEntries($pagingModel) as $relatedRecordModel) {
			++$counter;
			$row = [];
			foreach (['notes_title', 'filename'] as $fieldName) {
				$value = $relatedRecordModel->getDisplayValue($fieldName, false, true);
				$value = trim($value);
				$row[] = 'filename' === $fieldName ? "({$value})" : $value;
			}
			$rows[] = "{$counter}. " . implode(', ', $row);
			if ($attachFiles && $relatedRecordModel->checkFileIntegrity() &&
			  ($info = $relatedRecordModel->getFileDetails()) &&
			  ($filePath = $info['path'] . $info['attachmentsid']) &&
			  !isset($this->textParser->attachFiles[$filePath])
			) {
				$this->textParser->attachFiles[$filePath] = ['name' => $info['name'], 'path' => $filePath];
			}
		}
		return empty($rows) ? '' : implode('<br>', $rows);
	}
}
