<?php
/**
 * Related records data.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * Related records data class.
 */
class RelatedRecordsData extends Base
{
	/** @var string Class name */
	public $name = 'LBL_RECORDS_LIST_DATA_TEMPLATE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = "YFParser('\$(custom : RelatedRecordsData|__RELATION_MODULE_OR_RELATION_ID__|__FIELDS__|__CONDITIONS__|__LIMIT__|__ORDER_BY__|__RELATION_CONDITION__)\$')";

	/**
	 * Process.
	 *
	 * @return array
	 */
	public function process()
	{
		$data = [];
		[$relationId, $fields, $conditions, $limit, $orderBy, $relConditions] = array_pad($this->params, 6, '');
		if (is_numeric($relationId)) {
			$relatedModuleName = \App\Relation::getById($relationId)['related_modulename'] ?? '';
		} else {
			$relatedModuleName = $relationId;
			$relationId = \App\Relation::getRelationId($this->textParser->moduleName, $relatedModuleName);
		}
		if (!$this->textParser->recordModel
			|| !\App\Privilege::isPermitted($relatedModuleName)
			|| !($relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relatedModuleName, $relationId))
		) {
			return $data;
		}
		if ($conditions) {
			$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition(\App\Json::decode($conditions));
			$relationListView->set('search_params', $transformedSearchParams);
		}
		if (!empty($limit)) {
			$relationListView->getQueryGenerator()->setLimit((int) $limit);
		}
		if ($relConditions) {
			$relationListView->set('search_rel_params', \App\Json::decode($relConditions));
		}
		if ($orderBy) {
			$relationListView->set('orderby', $orderBy);
		}

		$fields = explode(',', $fields);
		$relationListView->setFields($fields);
		$fieldsModel = array_intersect_key($relationListView->getHeaders(), array_flip($fields));
		$data = [];
		foreach ($relationListView->getAllEntries() as $id => $relatedRecordModel) {
			if ($id === $this->textParser->recordModel->getId()) {
				continue;
			}
			foreach ($fieldsModel as $fieldModel) {
				if ($fieldModel->get('fromOutsideList') || $fieldModel->isViewable()) {
					$data[$id][$fieldModel->getName()] = $fieldModel->getDisplayValue($relatedRecordModel->get($fieldModel->getName()), $id, $relatedRecordModel, true);
				}
			}
		}
		return $data;
	}
}
