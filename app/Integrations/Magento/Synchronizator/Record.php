<?php
/**
 * Record abstract class.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Record class to synchronization.
 */
abstract class Record extends Base
{
	/**
	 * Format records id to given source.
	 *
	 * @param $ids
	 * @param string $formatTo
	 *
	 * @return array
	 */
	public function getFormatedRecordsIds($ids, $formatTo = 'magento'): array
	{
		$parsedIds = [];
		$mapIds = $this->map;
		if ('yetiforce' === $formatTo) {
			$mapIds = $this->mapCrm;
		}
		foreach ($ids as $id) {
			if (isset($mapIds[$id])) {
				$parsedIds[] = $mapIds[$id];
			}
		}
		return $parsedIds;
	}

	/**
	 * Method to compare changes of given two records.
	 *
	 * @param array $recordCrm
	 * @param array $record
	 *
	 * @return bool
	 */
	public function hasChanges(array $recordCrm, array $record): bool
	{
		$hasChanges = false;
		foreach ($this->mappedFields as $fieldCrm => $field) {
			if (isset($recordCrm[$fieldCrm], $record[$field]) && $recordCrm[$fieldCrm] !== $record[$field]) {
				$hasChanges = true;
			}
		}
		return $hasChanges;
	}

	/**
	 * Method to get which record have to update.
	 *
	 * @param array $recordCrm
	 * @param array $record
	 *
	 * @throws \ReflectionException
	 *
	 * @return bool|string
	 */
	public function whichToUpdate(array $recordCrm, array $record)
	{
		$toUpdate = false;
		$modifiedTimeCrmEnd = strtotime($recordCrm['modifiedtime']) > strtotime($this->lastScan['end_date']);
		$modifiedTimeCrmStart = strtotime($recordCrm['modifiedtime']) < strtotime($this->lastScan['start_date']);
		$modifiedTimeEnd = strtotime($record['updated_at']) > strtotime($this->getFormattedTime($this->lastScan['end_date']));
		$modifiedTimeStart = strtotime($record['updated_at']) < strtotime($this->getFormattedTime($this->lastScan['start_date']));
		if (($modifiedTimeCrmEnd && $modifiedTimeCrmStart) && ($modifiedTimeEnd && $modifiedTimeStart)) {
			$masterSource = \App\Config::component('Magento', 'masterSource');
			$toUpdate = 'magento' === $masterSource ? 'yetiforce' : 'magento';
		} elseif ($modifiedTimeCrmEnd && $modifiedTimeCrmStart) {
			$toUpdate = 'magento';
		} elseif ($modifiedTimeEnd && $modifiedTimeStart) {
			$toUpdate = 'yetiforce';
		}
		return $toUpdate;
	}
}
