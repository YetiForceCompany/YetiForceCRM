<?php
/**
 * TotalContacts class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Summary block total contacts for module leads.
 */
class TotalContacts
{
	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name = 'Total contacts';

	/**
	 * Sequence.
	 *
	 * @var int
	 */
	public $sequence = 6;

	/**
	 * Reference.
	 *
	 * @var string
	 */
	public $reference = 'Contacts';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering TotalContacts::process() method ...');
		$count = (new \App\Db\Query())
			->from('vtiger_contactdetails')
			->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_contactdetails.parentid' => 44])
			->count();
		\App\Log::trace('Exiting TotalContacts::process() method ...');

		return $count;
	}
}
