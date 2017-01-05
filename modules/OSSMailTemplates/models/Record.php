<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSMailTemplates_Record_Model extends Vtiger_Record_Model
{

	public static function getTempleteList($module)
	{
		$query = (new \App\Db\Query)->from('vtiger_ossmailtemplates')->where(['oss_module_list' => $module]);
		$dataReader = $query->createCommand()->query();
		$list = [];

		while ($row = $dataReader->read()) {
			$list[$row['ossmailtemplatesid']] = $row;
		}
		return $list;
	}
}
