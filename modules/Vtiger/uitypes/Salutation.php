<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Salutation_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Salutation.tpl';
	}

	/** {@inheritdoc} */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Salutation.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}
}
