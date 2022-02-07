<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Function to update sequence of several records.
	 *
	 * @param array $sequencesList
	 */
	public function updateSequence($sequencesList)
	{
		$db = App\Db::getInstance();
		$caseSequence = 'CASE';
		foreach ($sequencesList as $sequence => $recordId) {
			$caseSequence .= ' WHEN ' . $db->quoteColumnName('id') . ' = ' . $db->quoteValue($recordId) . ' THEN ' . $db->quoteValue($sequence);
		}
		$caseSequence .= ' END';
		$db->createCommand()->update('u_#__countries', ['sortorderid' => new yii\db\Expression($caseSequence)])->execute();
	}

	/**
	 * Update "status".
	 *
	 * @param int $id
	 * @param int $status
	 *
	 * @return int number of rows affected by the execution
	 */
	public function updateStatus($id, $status)
	{
		return App\Db::getInstance()->createCommand()
			->update('u_#__countries', ['status' => $status], ['id' => $id])
			->execute();
	}

	/**
	 * Update all statuses.
	 *
	 * @param int $status
	 *
	 * @return int number of rows affected by the execution
	 */
	public function updateAllStatuses($status)
	{
		return App\Db::getInstance()->createCommand()
			->update('u_#__countries', ['status' => $status])
			->execute();
	}

	/**
	 * Update "phone".
	 *
	 * @param int $id
	 * @param int $phone
	 *
	 * @return int number of rows affected by the execution
	 */
	public function updatePhone($id, $phone)
	{
		return App\Db::getInstance()->createCommand()
			->update('u_#__countries', ['phone' => $phone], ['id' => $id])
			->execute();
	}

	/**
	 * Update "uitype".
	 *
	 * @param int $id
	 * @param int $uitype
	 *
	 * @return int number of rows affected by the execution
	 */
	public function updateUitype($id, $uitype)
	{
		return App\Db::getInstance()->createCommand()
			->update('u_#__countries', ['uitype' => $uitype], ['id' => $id])
			->execute();
	}
}
