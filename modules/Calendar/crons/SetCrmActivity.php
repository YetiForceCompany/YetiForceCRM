<?php
/**
 * Set activity statistics.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Calendar_SetCrmActivity_Cron class.
 */
class Calendar_SetCrmActivity_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$pauser = \App\Pauser::getInstance('CrmActivity');
		$lastId = (int) $pauser->getValue();

		$dataReader = $this->getQuery($lastId)->createCommand()->query();
		if ($lastId && !$dataReader->count()) {
			$pauser->destroy();
			$dataReader = $this->getQuery()->createCommand()->query();
		}

		while ($row = $dataReader->read()) {
			Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
			$pauser->setValue((string) $row['crmid']);
			if ($this->checkTimeout()) {
				break;
			}
		}
		$dataReader->close();
	}

	/**
	 * Gets query.
	 *
	 * @param int $lastId
	 *
	 * @return App\Db\Query
	 */
	private function getQuery(int $lastId = null): App\Db\Query
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_tab', 'vtiger_tab.name=vtiger_crmentity.setype')
			->innerJoin('vtiger_field', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['vtiger_field.tablename' => 'vtiger_entity_stats', 'vtiger_field.fieldname' => 'crmactivity'],
				['not', ['vtiger_entity_stats.crmactivity' => null]],
				['not', ['vtiger_field.presence' => 1]]
			])->orderBy(['vtiger_crmentity.crmid' => SORT_ASC]);
		if ($lastId) {
			$query->andWhere(['>', 'vtiger_crmentity.crmid', $lastId]);
		}
		return $query;
	}
}
