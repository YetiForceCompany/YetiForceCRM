<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Currency_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Changes value.
	 *
	 * @var array
	 */
	protected $changes = [];

	/**
	 * Return currency id.
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Return currency name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('currency_name');
	}

	/**
	 * Check if currency is base.
	 *
	 * @return bool
	 */
	public function isBaseCurrency()
	{
		return ('-11' != $this->get('defaultid')) ? false : true;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = $recordLinks = [];
		if ($this->isBaseCurrency()) {
			//NO Edit and delete link for base currency
			return [];
		}
		$recordLinks[] = [
			'linkurl' => "javascript:Settings_Currency_Js.triggerDefault(event, '" . $this->getId() . "')",
			'linklabel' => 'LBL_SET_AS_DEFAULT',
			'linkclass' => 'btn-warning btn-sm',
			'linkicon' => 'fas fa-redo-alt',
		];

		$recordLinks[] = [
			'linkurl' => "javascript:Settings_Currency_Js.triggerEdit(event, '" . $this->getId() . "')",
			'linklabel' => 'LBL_EDIT',
			'linkclass' => 'btn-info btn-sm',
			'linkicon' => 'yfi yfi-full-editing-view',
		];
		$recordLinks[] = [
			'linkurl' => "javascript:Settings_Currency_Js.triggerDelete(event,'" . $this->getId() . "')",
			'linklabel' => 'LBL_DELETE',
			'linkclass' => 'btn-sm btn-danger',
			'linkicon' => 'fas fa-trash-alt',
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * return delete state of record.
	 *
	 * @return int
	 */
	public function getDeleteStatus()
	{
		if ($this->has('deleted')) {
			return $this->get('deleted');
		}
		//by default non deleted
		return 0;
	}

	/** {@inheritdoc} */
	public function set($key, $value)
	{
		if (null !== $this->getId() && $this->value[$key] !== $value) {
			$this->changes[$key] = $this->get($key);
		}
		$this->value[$key] = $value;

		return $this;
	}

	/**
	 * Populate changes to database.
	 *
	 * @return int
	 */
	public function save()
	{
		$db = \App\Db::getInstance();
		$id = $this->getId();
		$tableName = Settings_Currency_Module_Model::TABLE_NAME;
		if (!empty($id)) {
			$db->createCommand()->update($tableName, [
				'currency_name' => $this->get('currency_name'),
				'currency_code' => $this->get('currency_code'),
				'currency_status' => $this->get('currency_status'),
				'currency_symbol' => $this->get('currency_symbol'),
				'conversion_rate' => $this->isBaseCurrency() ? 1 : $this->get('conversion_rate'),
				'defaultid' => $this->get('defaultid'),
				'deleted' => $this->getDeleteStatus(),
			], ['id' => $id])->execute();
			if (isset($this->changes['defaultid'])) {
				$db->createCommand()->update($tableName, ['defaultid' => 0], ['and', ['defaultid' => -11], ['not', ['id' => $id]]])->execute();
			}
		} else {
			$db->createCommand()
				->insert($tableName, [
					'currency_name' => $this->get('currency_name'),
					'currency_code' => $this->get('currency_code'),
					'currency_status' => $this->get('currency_status'),
					'currency_symbol' => $this->get('currency_symbol'),
					'conversion_rate' => $this->get('conversion_rate'),
					'defaultid' => 0,
					'deleted' => 0,
				])->execute();
			$id = $db->getLastInsertID('vtiger_currency_info_id_seq');
		}
		\App\Fields\Currency::clearCache();

		return $id;
	}

	/**
	 * Returns instance of self.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getInstance($id)
	{
		$db = (new App\Db\Query())->from(Settings_Currency_Module_Model::TABLE_NAME);
		if (vtlib\Utils::isNumber($id)) {
			$query = $db->where(['id' => $id]);
		} else {
			$query = $db->where(['currency_name' => $id]);
		}
		$row = $query->createCommand()->queryOne();
		if ($row) {
			$instance = new self();
			$instance->setData($row);
			return $instance;
		}
	}

	/**
	 * Return all non mapped currences.
	 *
	 * @param array $includedIds
	 *
	 * @return \Settings_Currency_Record_Model[]
	 */
	public static function getAllNonMapped($includedIds = [])
	{
		if (!\is_array($includedIds)) {
			if (!empty($includedIds)) {
				$includedIds = [$includedIds];
			} else {
				$includedIds = [];
			}
		}
		$query = (new \App\Db\Query())->select(['vtiger_currencies.*'])->from('vtiger_currencies')->leftJoin('vtiger_currency_info', 'vtiger_currency_info.currency_name = vtiger_currencies.currency_name')->where(['or', ['vtiger_currency_info.currency_name' => null], ['vtiger_currency_info.deleted' => 1]]);
		if (!empty($includedIds)) {
			$query->orWhere(['vtiger_currency_info.id' => $includedIds]);
		}
		$currencyModelList = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modelInstance = new self();
			$modelInstance->setData($row);
			$currencyModelList[$row['currencyid']] = $modelInstance;
		}
		$dataReader->close();

		return $currencyModelList;
	}

	/**
	 * Return currences.
	 *
	 * @param array $excludedIds
	 *
	 * @return \Settings_Currency_Record_Model[]
	 */
	public static function getAll($excludedIds = [])
	{
		$query = (new App\Db\Query())->from(Settings_Currency_Module_Model::TABLE_NAME)
			->where(['deleted' => 0, 'currency_status' => 'Active']);
		if (!empty($excludedIds)) {
			$query->andWhere(['<>', 'id', $excludedIds]);
		}
		$dataReader = $query->createCommand()->query();
		$instanceList = [];
		while ($row = $dataReader->read()) {
			$instanceList[$row['id']] = new self($row);
		}
		$dataReader->close();

		return $instanceList;
	}
}
