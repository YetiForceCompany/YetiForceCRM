<?php

/**
 * MultiDomain class.
 *
 * @package   App
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
	 * Find crm ids with specified domain.
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
			$crmids = $queryGenerator->createQuery()->column();
		}
		return $crmids;
	}
}
