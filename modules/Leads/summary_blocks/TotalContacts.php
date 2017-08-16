<?php
/**
 * TotalContacts class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Summary block total contacts for module leads
 */
class TotalContacts
{

	/**
	 * Name
	 * @var string
	 */
	public $name = 'Total contacts';

	/**
	 * Sequence
	 * @var int
	 */
	public $sequence = 6;

	/**
	 * Reference
	 * @var string
	 */
	public $reference = 'Contacts';

	/**
	 * Process
	 * @param object $instance
	 * @return int
	 */
	public function process($instance)
	{

		\App\Log::trace("Entering TotalContacts::process() method ...");
		$count = $query = (new \App\Db\Query())->from('vtiger_contactdetails')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid=vtiger_contactdetails.contactid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_contactdetails.parentid' => $instance->getId()])->count();
		\App\Log::trace("Exiting TotalContacts::process() method ...");
		return $count;
	}
}
