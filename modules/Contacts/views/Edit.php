<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Contacts_Edit_View extends Vtiger_Edit_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $this->record->getModule());
		// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7851
		$salutationType = $request->getByType('salutationtype', 'Text');
		if (!empty($salutationType)) {
			$salutationFieldModel->set('fieldvalue', $salutationFieldModel->getUITypeModel()->getDBValue($salutationType, $this->record));
		} else {
			$salutationFieldModel->set('fieldvalue', $this->record->get('salutationtype'));
		}
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);
		parent::process($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		if ($this->record->isNew()) {
			return parent::getPageTitle($request);
		} else {
			$moduleName = $request->getModule();
			return \App\Language::translate($moduleName, $moduleName) . ' ' .
				\App\Language::translate('LBL_EDIT') . ' ' . $this->record->getDisplayName();
		}
	}
}
