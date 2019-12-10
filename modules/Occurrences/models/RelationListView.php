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
			if($this->getRelationModel()->get('name') === 'getRelatedContacts'){
				$field = new \Vtiger_Field_Model();
				$field->set('name', 'role_rel')
					->set('column', 'role_rel')
					->set('label', 'LBL_ROLE_REL')
					->set('uitype', 16)
					->set('typeofdata', 'V~O')
					->set('fromOutsideList', true)
					->setModule($moduleModel);
				$headerFields[$field->getName()] = $field;
			} else if($this->getRelationModel()->get('name') === 'getRelatedMembers'){
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
					->set('uitype', 7)
					->set('typeofdata', 'I~O')
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
}
