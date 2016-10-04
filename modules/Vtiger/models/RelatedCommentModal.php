<?php

/**
 * Basic RelatedCommentModal Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_Model extends Vtiger_Base_Model
{

	public static function getInstance($record, $moduleName, $relatedRecord, $relatedModuleName)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'RelatedCommentModal', $moduleName);
		$instance = new $modelClassName();

		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);
		$instance->set('relationListView', $relationListView)
			->set('record', $record)
			->set('moduleName', $moduleName)
			->set('relatedRecord', $relatedRecord)
			->set('relatedModuleName', $relatedModuleName);
		return $instance;
	}

	public function getComment()
	{
		$db = PearDatabase::getInstance();
		if (substr($this->get('relatedRecord'), 0, 1) == 'T') {
			$result = $db->pquery($this->getRelationTreeQuery(), [$this->get('record'), $this->get('relatedRecord'), vtlib\Functions::getModuleId($this->get('relatedModuleName'))]);
		} else {
			$result = $db->pquery($this->getRelationQuery(), [$this->get('record'), $this->get('relatedRecord')]);
		}
		if ($db->getRowCount($result)) {
			return $db->getSingleValue($result);
		}
		return '';
	}

	public function getRelationQuery()
	{
		$relationTable = $this->getRelationTable();
		$table = key($relationTable);
		$query = sprintf('SELECT rel_comment FROM %s WHERE %s = ? && %s = ?', $table, $relationTable[$table][0], $relationTable[$table][1]);
		return $query;
	}

	public function getRelationTable()
	{
		$instance = CRMEntity::getInstance($this->get('moduleName'));
		if (method_exists($instance, 'setRelationTables')) {
			$relationTable = $instance->setRelationTables($this->get('relatedModuleName'));
		}
		if (empty($relationTable)) {
			$relationTable = ['vtiger_crmentityrel' => ['crmid', 'relcrmid'], $instance->table_name => $instance->table_index];
		}
		return $relationTable;
	}

	public function getRelationTreeQuery()
	{
		return 'SELECT rel_comment FROM u_yf_crmentity_rel_tree WHERE crmid = ? && tree = ? && relmodule = ?';
	}

	public function isEditable()
	{
		return $this->get('relationListView')->getRelationModel()->get('relation_comment');
	}

	public function save($comment)
	{
		$db = PearDatabase::getInstance();
		if (substr($this->get('relatedRecord'), 0, 1) == 'T') {
			$db->update('u_yf_crmentity_rel_tree', [
				'rel_comment' => $comment
				], 'crmid = ? && tree = ? && relmodule = ?', [$this->get('record'), $this->get('relatedRecord'), vtlib\Functions::getModuleId($this->get('relatedModuleName'))]
			);
		} else {
			$relationTable = $this->getRelationTable();
			$table = key($relationTable);
			$db->update($table, [
				'rel_comment' => $comment
				], $relationTable[$table][0] . ' = ? && ' . $relationTable[$table][1] . ' = ?', [$this->get('record'), $this->get('relatedRecord')]
			);
		}
	}
}
