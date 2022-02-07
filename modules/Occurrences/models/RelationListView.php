<?php
/**
 * Relations.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Occurrences_RelationListView_Model extends Vtiger_RelationListView_Model
{
	/** {@inheritdoc} */
	public function getLinks(): array
	{
		$relatedLink = parent::getLinks();
		if (!$this->getParentRecordModel()->isReadOnly()) {
			$relationModelInstance = $this->getRelationModel();
			$relatedModuleModel = $relationModelInstance->getRelationModuleModel();
			$contactModel = Vtiger_Module_Model::getInstance('Contacts');
			if ('Contacts' === $relatedModuleModel->getName() && $relatedModuleModel->isPermitted('MassComposeEmail') && App\Config::main('isActiveSendingMails') && App\Mail::getDefaultSmtp()) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_MASS_SEND_EMAIL',
					'linkurl' => 'javascript:Vtiger_RelatedList_Js.triggerSendEmail();',
					'linkicon' => 'fas fa-envelope',
				]);
			}
			if ($contactModel->getName() === $relatedModuleModel->getName() && $contactModel->isActive()
				&& $contactModel->isPermitted('CreateView') && $contactModel->isPermitted('Import')
				&& $relationModelInstance->isAddActionSupported()
				&& $this->getParentRecordModel()->isViewable()
				&& $relationModelInstance->getParentModuleModel()->getName() === $this->getParentRecordModel()->getModuleName()
		) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_IMPORT',
					'linkurl' => $contactModel->getImportUrl() . '&relationId=' . $relationModelInstance->getId() . '&src_record=' . $this->getParentRecordModel()->getId(),
					'linkicon' => 'fas fa-download',
				]);
			}
		}
		return $relatedLink;
	}
}
