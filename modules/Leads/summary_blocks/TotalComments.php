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

class TotalComments
{

	public $name = 'Total comments';
	public $sequence = 2;
	public $reference = 'Comments';

	public function process($instance)
	{
		
		\App\Log::trace("Entering TotalComments::process() method ...");
		$adb = PearDatabase::getInstance();
		$modcomments = 'SELECT COUNT(modcommentsid) AS comments FROM vtiger_modcomments
			WHERE vtiger_modcomments.related_to = ?';
		$result_modcomments = $adb->pquery($modcomments, array($instance->getId()));
		$count = $adb->query_result($result_modcomments, 0, 'comments');
		\App\Log::trace("Exiting TotalComments::process() method ...");
		return $count;
	}
}
