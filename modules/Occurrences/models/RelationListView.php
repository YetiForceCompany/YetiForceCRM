<?php
/**
 * Relations.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Occurrences_RelationListView_Model extends Vtiger_RelationListView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if (\in_array($this->getRelationModel()->get('modulename'), ['Contacts', 'Occurrences'])) {
			$moduleModel = Vtiger_Module_Model::getInstance('Occurrences');
			if ('getRelatedContacts' === $this->getRelationModel()->get('name')) {
				$field = new \Vtiger_Field_Model();
				$field->set('name', 'role_rel')
					->set('column', 'role_rel')
					->set('label', 'LBL_ROLE_REL')
					->set('uitype', 16)
					->set('typeofdata', 'V~O')
					->set('fromOutsideList', true)
					->setModule($moduleModel);
				$headerFields[$field->getName()] = $field;
			} elseif ('getRelatedMembers' === $this->getRelationModel()->get('name')) {
				$field = new \Vtiger_Field_Model();
				$field->set('name', 'status_rel')
					->set('column', 'status_rel')
					->set('label', 'LBL_STATUS_REL')
					->set('uitype', 16)
					->set('typeofdata', 'V~O')
					->set('fromOutsideList', true)
					->setModule($moduleModel);
				$headerFields[$field->getName()] = $field;
				$field = new \Vtiger_Field_Model();
				$field->set('name', 'rating_rel')
					->set('column', 'rating_rel')
					->set('label', 'LBL_RATING_REL')
					->set('uitype', 16)
					->set('typeofdata', 'V~O')
					->set('fromOutsideList', true)
					->setModule($moduleModel);
				$headerFields[$field->getName()] = $field;
				$field = new \Vtiger_Field_Model();
				$field->set('name', 'comment_rel')
					->set('column', 'comment_rel')
					->set('label', 'LBL_COMMENT_REL')
					->set('uitype', 21)
					->set('typeofdata', 'V~O')
					->set('fromOutsideList', true)
					->setModule($moduleModel);
				$headerFields[$field->getName()] = $field;
			}
		}
		return $headerFields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLinks()
	{
		$relatedLink = parent::getLinks();
		$relationModelInstance = $this->getRelationModel();
		$relatedModuleModel = $relationModelInstance->getRelationModuleModel();
		if ($relatedModuleModel->isPermitted('MassComposeEmail') && App\Config::main('isActiveSendingMails') && App\Mail::getDefaultSmtp()) {
			if ('Contacts' === $relatedModuleModel->getName()) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_MASS_SEND_EMAIL',
					'linkurl' => 'javascript:Vtiger_RelatedList_Js.triggerSendEmail();',
					'linkicon' => 'fas fa-envelope',
				]);
			}
		}
		return $relatedLink;
	}
}
