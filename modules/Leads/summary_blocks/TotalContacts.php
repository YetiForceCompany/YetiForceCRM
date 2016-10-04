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

class TotalContacts
{

	public $name = 'Total contacts';
	public $sequence = 6;
	public $reference = 'Contacts';

	public function process($instance)
	{
		
		\App\Log::trace("Entering TotalContacts::process() method ...");
		$adb = PearDatabase::getInstance();
		$contact = 'SELECT COUNT(contactid) as count FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_contactdetails.contactid
			WHERE vtiger_crmentity.deleted=0 &&  vtiger_contactdetails.parentid = ?';
		$result_contact = $adb->pquery($contact, array($instance->getId()));
		$count = $adb->query_result($result_contact, 0, 'count');
		\App\Log::trace("Exiting TotalContacts::process() method ...");
		return $count;
	}
}
