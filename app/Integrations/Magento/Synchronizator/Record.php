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
	 * @param array $ids
	 * @param string $formatTo
	 *
	 * @return array
	 */
	public function getFormatedRecordsIds(array $ids, $formatTo = self::MAGENTO): array
	{
		$parsedIds = [];
		$mapIds = $this->map;
		if (self::YETIFORCE === $formatTo) {
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
		$modifiedTimeCrm = strtotime($recordCrm['modifiedtime']);
		$updatedTime = strtotime($record['updated_at']);
		$modifiedTimeCrmEnd = $modifiedTimeCrm > strtotime($this->lastScan['end_date']);
		$modifiedTimeCrmStart = $modifiedTimeCrm < strtotime($this->lastScan['start_date']);
		$modifiedTimeEnd = $updatedTime > strtotime($this->getFormattedTime($this->lastScan['end_date']));
		$modifiedTimeStart = $updatedTime < strtotime($this->getFormattedTime($this->lastScan['start_date']));
		if ($modifiedTimeCrmEnd && $modifiedTimeCrmStart && $modifiedTimeEnd && $modifiedTimeStart) {
			$toUpdate = 'magento' === \App\Config::component('Magento', 'masterSource') ? self::YETIFORCE : self::MAGENTO;
		} elseif ($modifiedTimeCrmEnd && $modifiedTimeCrmStart) {
			$toUpdate = self::MAGENTO;
		} elseif ($modifiedTimeEnd && $modifiedTimeStart) {
			$toUpdate = self::YETIFORCE;
		}
		return $toUpdate;
	}
}
