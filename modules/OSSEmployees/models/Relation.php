<?php

/**
 * Relation Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author 	  Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSEmployees_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function privilegeToDelete(): bool
	{
		return 'OSSTimeControl' !== $this->getRelationModuleName() && parent::privilegeToDelete();
	}
}
