<?php
/**
 * Duplicate checker handler file.
 *
 * @package   Handler
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Duplicate checker handler class.
 */
class Reservations_DuplicateChecker_Handler
{
	/** @var bool Search archived and deleted (true - Yes, false - No) */
	const TRASH_ARCHIVE = false;

	/** @var string[] List of fields from which the combination of values must be unique */
	const FIELDS = ['type'];

	/**
	 * EditViewPreSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 * @param array            $handler
	 */
	public function editViewPreSave(App\EventHandler $eventHandler, array $handler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$moduleName = $recordModel->getModuleName();
		$response = ['result' => true];
		$query = $this->getQuery($recordModel);
		if ($rows = $query->all()) {
			$allowSave = true;
			$info = '';
			foreach ($rows as $row) {
				$info .= '- ' . \App\Record::getHtmlLink($row['id'], $moduleName) . '<br>';
				if ('PLL_ACCEPTED' === $row['reservations_status']) {
					$allowSave = false;
				}
			}
			$response = [
				'result' => false,
				'message' => App\Language::translate('LBL_RESERVATION_EXISTS', $moduleName) . '<br>' . $info
			];
			if ($allowSave) {
				$response['type'] = 'confirm';
				$response['hash'] = hash('sha256', implode('|', $recordModel->getData()));
			}
		}
		return $response;
	}

	/**
	 * Get query to verify uniqueness.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return App\Db\Query
	 */
	protected function getQuery(Vtiger_Record_Model $recordModel): App\Db\Query
	{
		$queryGenerator = new \App\QueryGenerator('Reservations');
		if (self::TRASH_ARCHIVE) {
			$queryGenerator->setStateCondition('All');
		}
		$queryGenerator->setFields(['id', 'reservations_status'])->permissions = false;
		$queryGenerator->addCondition('reservations_status', 'PLL_CANCELLED', 'n');
		if ($recordModel->getId()) {
			$queryGenerator->addCondition('id', $recordModel->getId(), 'n');
		}
		foreach (self::FIELDS as $field) {
			$queryGenerator->addCondition($field, $recordModel->get($field), 'e');
		}
		$startDateTime = $recordModel->get('date_start') . ' ' . $recordModel->get('time_start');
		$endDateTime = $recordModel->get('due_date') . ' ' . $recordModel->get('time_end');
		$query = $queryGenerator->createQuery();
		$query->andWhere(['or',
			[
				'and',
				['<=', new \yii\db\Expression("CONCAT(date_start, ' ', time_start)"), $endDateTime],
				['>=', new \yii\db\Expression("CONCAT(due_date, ' ', time_end)"), $startDateTime]
			],
			[
				'and',
				['>', new \yii\db\Expression("CONCAT(date_start, ' ', time_start)"), $startDateTime],
				['<', new \yii\db\Expression("CONCAT(due_date, ' ', time_end)"), $endDateTime]
			],
		]);
		return $query;
	}
}
