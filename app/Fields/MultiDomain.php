<?php

/**
 * MultiDomain class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @param string $moduleName
	 * @param string $fieldName
	 * @param string $domain
	 *
	 * @return int[]
	 */
	public static function findIdByDomain(string $moduleName, string $fieldName, string $domain)
	{
		$ids = [];
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permissions = false;
		if ($queryGenerator->getModuleField($fieldName)) {
			$queryGenerator->setFields(['id']);
			$queryGenerator->addCondition($fieldName, $domain, 'a');
			$ids = $queryGenerator->createQuery()->column();
		}
		return $ids;
	}
}
