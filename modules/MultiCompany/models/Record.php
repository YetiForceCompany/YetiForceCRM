<?php

/**
 * Record Class for MultiCompany.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultiCompany_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of IStorages Hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $id => $storageInfo) {
			preg_match('/<a href="+/', $storageInfo[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $storageInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i", $storageInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('MultiCompany');
				$recordModel->setId($id);
				$hierarchy['entries'][$id][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save()
	{
		parent::save();
		if ($this->getPreviousValue('logo')) {
			\App\UserPrivilegesFile::reloadByMultiCompany($this->getId());
		}
	}
}
