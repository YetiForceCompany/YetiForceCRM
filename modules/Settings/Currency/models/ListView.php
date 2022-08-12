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

class Settings_Currency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function getBasicListQuery(): App\Db\Query
	{
		$query = parent::getBasicListQuery();
		$query->where(['deleted' => 0]);

		return $query;
	}
}
