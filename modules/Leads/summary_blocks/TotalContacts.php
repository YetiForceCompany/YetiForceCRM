<?php

/**
 * TotalContacts class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
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
