<?php

/**
 * Record Class for Competition.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Competition_Record_Model extends Vtiger_Record_Model
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
		foreach ($hierarchy['entries'] as $competitionId => $data) {
			preg_match('/<a href="+/', $data[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $data[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $data[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Competition');
				$recordModel->setId($competitionId);
				$hierarchy['entries'][$competitionId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] .
					'</a>';
			}
		}
		return $hierarchy;
	}
}
