<?php

/**
 * Basic RelatedCommentModal Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_Model extends \App\Base
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
		if ('T' === substr($this->get('relatedRecord'), 0, 1)) {
			$dataReader = $this->getRelationTreeQuery()->createCommand()->query();
		} else {
			$dataReader = $this->getRelationQuery()->createCommand()->query();
		}
		if ($result = $dataReader->readColumn(0)) {
			return $result;
		}
		return '';
	}

	public function getRelationQuery()
	{
		$relationTable = $this->getRelationTable();
		$table = key($relationTable);

		return (new \App\Db\Query())->select(['rel_comment'])
			->from($table)
			->where([$relationTable[$table][0] => $this->get('record'), $relationTable[$table][1] => $this->get('relatedRecord')]);
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
		return (new \App\Db\Query())->select(['rel_comment'])
			->from('u_#__crmentity_rel_tree')
			->where(['crmid' => $this->get('record'), 'tree' => $this->get('relatedRecord'), 'relmodule' => App\Module::getModuleId($this->get('relatedModuleName'))]);
	}

	public function isEditable(): bool
	{
		return $this->get('relationListView')->getRelationModel()->get('relation_comment');
	}

	public function save($comment)
	{
		$db = App\Db::getInstance();
		if ('T' === substr($this->get('relatedRecord'), 0, 1)) {
			$db->createCommand()->update('u_#__crmentity_rel_tree', [
				'rel_comment' => $comment,
			], ['crmid' => $this->get('record'), 'tree' => $this->get('relatedRecord'), 'relmodule' => App\Module::getModuleId($this->get('relatedModuleName'))]
			)->execute();
		} else {
			$relationTable = $this->getRelationTable();
			$table = key($relationTable);
			$db->createCommand()->update($table, [
				'rel_comment' => $comment,
			], [$relationTable[$table][0] => $this->get('record'), $relationTable[$table][1] => $this->get('relatedRecord')]
			)->execute();
		}
	}
}
