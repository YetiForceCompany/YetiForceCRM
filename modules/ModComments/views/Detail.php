<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class ModComments_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}

	/** {@inheritdoc} */
	protected function showBreadCrumbLine()
	{
		return false;
	}
}
