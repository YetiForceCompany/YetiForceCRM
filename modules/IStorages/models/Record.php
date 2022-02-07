<?php

/**
 * Record Class for IStorages.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of IStorages Hierarchy.
	 *
	 * @return <Array>
	 */
	public function getHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $storageId => $storageInfo) {
			preg_match('/<a href="+/', $storageInfo[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $storageInfo[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $storageInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('IStorages');
				$recordModel->setId($storageId);
				$hierarchy['entries'][$storageId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($fieldName, $record = false, $rawText = false, $length = false)
	{
		// This is special field / displayed only in Products module [view=Detail relatedModule=IStorages]
		if ('qtyinstock' === $fieldName) {
			return $this->get($fieldName);
		}
		return parent::getDisplayValue($fieldName, $record, $rawText, $length);
	}

	/**
	 * Function updates number of product in storage.
	 *
	 * @param int   $relatedRecordId - Product Id
	 * @param float $qty
	 *
	 * @return bool
	 */
	public function updateQtyProducts(int $relatedRecordId, float $qty): bool
	{
		$tableInfo = Vtiger_Relation_Model::getReferenceTableInfo('IStorages', 'Products');
		$isExists = (new \App\Db\Query())->from($tableInfo['table'])->where([$tableInfo['rel'] => $this->getId(), $tableInfo['base'] => $relatedRecordId])->exists();
		if ($isExists) {
			$status = App\Db::getInstance()->createCommand()
				->update($tableInfo['table'], ['qtyinstock' => $qty], [$tableInfo['rel'] => $this->getId(), $tableInfo['base'] => $relatedRecordId])
				->execute();
		} else {
			$status = App\Db::getInstance()->createCommand()
				->insert($tableInfo['table'], [
					$tableInfo['rel'] => $this->getId(),
					$tableInfo['base'] => $relatedRecordId,
					'qtyinstock' => $qty,
				])->execute();
		}
		return (bool) $status;
	}
}
