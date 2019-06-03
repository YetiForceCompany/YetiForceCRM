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
		$mapIds = $this->mapMagento;
		if ('yetiforce' === $formatTo) {
			$mapIds = $this->mapYF;
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
	 * @param array $recordYF
	 * @param array $recordMagento
	 *
	 * @return bool
	 */
	public function hasChanges(array $recordYF, array $recordMagento): bool
	{
		$hasChanges = false;
		foreach ($this->mappedFields as $fieldYF => $fieldMagento) {
			if (isset($recordYF[$fieldYF], $recordMagento[$fieldMagento]) && $recordYF[$fieldYF] !== $recordMagento[$fieldMagento]) {
				$hasChanges = true;
			}
		}
		return $hasChanges;
	}

	/**
	 * Method to get which record have to update.
	 *
	 * @param array $recordYF
	 * @param array $recordMagento
	 *
	 * @throws \ReflectionException
	 *
	 * @return bool|string
	 */
	public function whichToUpdate(array $recordYF, array $recordMagento)
	{
		$toUpdate = false;
		$modifiedTimeYFEnd = strtotime($recordYF['modifiedtime']) > strtotime($this->lastScan['end_date']);
		$modifiedTimeYFStart = strtotime($recordYF['modifiedtime']) < strtotime($this->lastScan['start_date']);
		$modifiedTimeMagentoEnd = strtotime($recordMagento['updated_at']) > strtotime($this->getFormattedTime($this->lastScan['end_date']));
		$modifiedTimeMagentoStart = strtotime($recordMagento['updated_at']) < strtotime($this->getFormattedTime($this->lastScan['start_date']));
		if (($modifiedTimeYFEnd && $modifiedTimeYFStart) && ($modifiedTimeMagentoEnd && $modifiedTimeMagentoStart)) {
			$masterSource = \App\Config::component('Magento', 'masterSource');
			$toUpdate = 'magento' === $masterSource ? 'yetiforce' : 'magento';
		} elseif ($modifiedTimeYFEnd && $modifiedTimeYFStart) {
			$toUpdate = 'magento';
		} elseif ($modifiedTimeMagentoEnd && $modifiedTimeMagentoStart) {
			$toUpdate = 'yetiforce';
		}
		return $toUpdate;
	}
}
