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
	 * @param array  $ids
	 * @param string $formatTo
	 * @param string $type
	 *
	 * @return array
	 */
	public function getFormattedRecordsIds(array $ids, int $formatTo, string $type): array
	{
		$parsedIds = [];
		$mapIds = $this->map[$type];
		if (self::YETIFORCE === $formatTo) {
			$mapIds = $this->mapCrm[$type];
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
	 * @param array $dataCrm
	 * @param array $data
	 *
	 * @return bool
	 */
	public function hasChanges(array $dataCrm, array $data): bool
	{
		$hasChanges = false;
		$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
		$productFields->setData($data);
		foreach ($productFields->getFields(true) as $fieldCrm => $field) {
			$fieldValue = $productFields->getFieldValue($field);
			if (\App\Purifier::decodeHtml($dataCrm[$fieldCrm]) != \App\Purifier::decodeHtml($fieldValue)) {
				$hasChanges = true;
				break;
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

	/**
	 * Method to check has images changes.
	 *
	 * @param $images
	 * @param $imagesCrmData
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function checkImages($images, $imagesCrmData)
	{
		$imagesCrm = $imagesAdd = $imagesRemove = $imagesCrmNames = [];
		$needUpdate = false;
		$imagesCrmData = \App\Json::decode($imagesCrmData['imagename']);
		if (!empty($imagesCrmData)) {
			$imagesCrmNames = str_replace([' ', '-', '_'], '', array_column($imagesCrmData, 'name'));
		}
		$images = $images['media_gallery_entries'] ?? [];
		foreach ($images as &$image) {
			$explodedPath = explode('/', $image['file']);
			$image['filename'] = end($explodedPath);
			if (empty($imagesCrmNames) || !\in_array(str_replace([' ', '-', '_'], '', $image['filename']), $imagesCrmNames)) {
				$imagesCrm[] = $image;
				$imagesRemove[] = $image;
				$needUpdate = true;
			}
		}
		if (!empty($imagesCrmData)) {
			$imagesNames = str_replace([' ', '-', '_'], '', array_column($images, 'filename'));
			foreach ($imagesCrmData as $imageCrm) {
				if (!\in_array(str_replace([' ', '-', '_'], '', $imageCrm['name']), $imagesNames)) {
					$imagesAdd[] = $imageCrm;
					$imagesRemoveCrm[] = $imageCrm;
					$needUpdate = true;
				} else {
					$imagesCrm[] = $imageCrm;
				}
			}
		}
		return $needUpdate ? ['addCrm' => $imagesCrm, 'add' => $imagesAdd, 'remove' => $imagesRemove] : [];
	}

	/**
	 * Method to get search criteria Magento records.
	 *
	 * @param string|array $ids
	 * @param int          $pageSize
	 *
	 * @return string
	 */
	public function getSearchCriteria($ids, int $pageSize = 10): string
	{
		if ('all' !== $ids) {
			$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][field]=entity_id';
			if (!empty($ids) && \is_array($ids)) {
				$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . implode(',', $ids);
				$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=in';
			} else {
				$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . $this->lastScan['id'];
				$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=gt';
				$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][field]=updated_at';
				$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][value]=' . $this->getFormattedTime($this->lastScan['start_date']);
				$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][condition_type]=lteq';
				if (!empty($this->lastScan['end_date'])) {
					$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][field]=updated_at';
					$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][value]=' . $this->getFormattedTime($this->lastScan['end_date']);
					$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][condition_type]=gteq';
				}
				$searchCriteria[] = 'searchCriteria[pageSize]=' . $pageSize;
			}
			$searchCriteria = implode('&', $searchCriteria);
		}

		return $searchCriteria ?? 'searchCriteria';
	}
}
