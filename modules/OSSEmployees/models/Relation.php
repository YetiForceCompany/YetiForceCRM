<?php

/**
 * Relation Model.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSEmployees_Relation_Model extends Vtiger_Relation_Model
{
	/** {@inheritdoc} */
	public function privilegeToDelete(Vtiger_Record_Model $recordModel = null, int $recordId = null): bool
	{
		return 'OSSTimeControl' !== $this->getRelationModuleName() && parent::privilegeToDelete($recordModel, $recordId);
	}
}
