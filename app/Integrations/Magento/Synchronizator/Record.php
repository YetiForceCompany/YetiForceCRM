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
	 *
	 * @return array
	 */
	public function getFormattedRecordsIds(array $ids, $formatTo = self::MAGENTO): array
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
	 * @param array $dataCrm
	 * @param array $data
	 *
	 * @return bool
	 */
	public function hasChanges(array $dataCrm, array $data): bool
	{
		$hasChanges = false;
		$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
		$productFields->setDataCrm($dataCrm);
		$productFields->setData($data);
		foreach ($productFields->getFields(true) as $fieldCrm => $field) {
			$fieldValueCrm = $productFields->getFieldValueCrm($fieldCrm);
			$fieldValue = $productFields->getFieldValue($field);
			if ($fieldValueCrm != $fieldValue) { //todo:rebuild
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
			$imagesCrmNames = array_column($imagesCrmData, 'name');
		}
		$images = $images['media_gallery_entries'];
		foreach ($images as &$image) {
			$explodedPath = explode('/', $image['file']);
			$image['filename'] = end($explodedPath);
			if (empty($imagesCrmNames) || !\in_array($image['filename'], $imagesCrmNames)) {
				$imagesCrm[] = $image;
				$imagesRemove[] = $image;
				$needUpdate = true;
			}
		}
		if (!empty($imagesCrmData)) {
			$imagesNames = array_column($images, 'filename');
			foreach ($imagesCrmData as $imageCrm) {
				if (!\in_array($imageCrm['name'], $imagesNames)) {
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
}
