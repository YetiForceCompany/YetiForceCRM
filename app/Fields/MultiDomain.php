<?php

/**
 * MultiDomain class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Fields;

/**
 * MultiDomain class.
 */
class MultiDomain
{
	/**
	 * Get crm ids.
	 *
	 * @param string              $domain
	 * @param \Vtiger_Field_Model $fieldModel
	 *
	 * @return int[]
	 */
	public static function findIdByDomain(string $domain, \Vtiger_Field_Model $fieldModel)
	{
		$crmids = [];
		$fieldName = $fieldModel->getName();
		$moduleName = $fieldModel->getModuleName();
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permissions = false;
		if ($queryGenerator->getModuleField($fieldName)) {
			$queryGenerator->setFields(['id']);
			$queryGenerator->addNativeCondition(['like', $fieldName, ",$domain,"]);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while (($crmid = $dataReader->readColumn(0)) !== false) {
				$crmids[] = $crmid;
			}
			$dataReader->close();
		}
		return $crmids;
	}
}
