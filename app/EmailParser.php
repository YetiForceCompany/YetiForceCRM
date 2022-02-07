<?php

namespace App;

/**
 * Email parser class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class EmailParser extends TextParser
{
	private static $permissionToSend = [
		'Accounts' => 'emailoptout',
		'Contacts' => 'emailoptout',
		'Users' => 'emailoptout',
		'Leads' => 'noapprovalemails',
	];
	public $emailoptout = true;

	/**
	 * Check if this content can be used.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param string              $moduleName
	 *
	 * @return bool
	 */
	protected function useValue($fieldModel, $moduleName)
	{
		if ($this->emailoptout && isset(self::$permissionToSend[$moduleName])) {
			$checkFieldName = self::$permissionToSend[$moduleName];
			$permissionFieldModel = $this->recordModel->getModule()->getField($checkFieldName);
			return ($permissionFieldModel && $permissionFieldModel->isActiveField() && $this->recordModel->has($checkFieldName)) ? (bool) $this->recordModel->get($checkFieldName) : true;
		}
		return true;
	}

	/**
	 * Get content parsed for emails.
	 *
	 * @param bool $trim
	 *
	 * @return array|string
	 */
	public function getContent($trim = false)
	{
		if (!$trim) {
			return $this->content;
		}
		$emails = [];
		foreach (explode(',', $this->content) as $content) {
			$content = trim($content);
			if (empty($content) || '-' === $content) {
				continue;
			}
			if (strpos($content, '&lt;') && strpos($content, '&gt;')) {
				[$fromName, $fromEmail] = explode('&lt;', $content);
				$fromEmail = rtrim($fromEmail, '&gt;');
				$emails[$fromEmail] = $fromName;
			} else {
				$emails[] = $content;
			}
		}
		return $emails;
	}

	/** {@inheritdoc} */
	protected function relatedRecordsListPrinter(\Vtiger_RelationListView_Model $relationListView, \Vtiger_Paging_Model $pagingModel, int $maxLength): string
	{
		$relatedModuleName = $relationListView->getRelationModel()->getRelationModuleName();
		$rows = '';
		$fields = $relationListView->getHeaders();
		foreach ($relationListView->getEntries($pagingModel) as $relatedRecordModel) {
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel && 'email' === $fieldModel->getFieldDataType() && $this->useValue($fieldModel, $relatedModuleName)) {
					$rows .= $relatedRecordModel->get($fieldName) . ',';
				}
			}
		}
		return rtrim($rows, ',');
	}
}
