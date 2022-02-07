<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ServiceContracts_GetDependentsList_Relation class.
 */
class ServiceContracts_GetDependentsList_Relation extends Vtiger_GetDependentsList_Relation
{
	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		if ('HelpDesk' === $this->relationModel->getRelationModuleName()) {
			$entityInstance = $this->relationModel->getParentModuleModel()->getEntityInstance();
			$entityInstance->updateHelpDeskRelatedTo($sourceRecordId, $destinationRecordId);
			$entityInstance->updateServiceContractState($sourceRecordId);
		}
		return true;
	}
}
