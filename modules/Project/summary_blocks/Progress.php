<?php

/**
 * Progress class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Progress
{
	public $name = 'Progress';
	public $sequence = 5;
	public $reference = 'Details';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		return $recordModel->get('progress');
	}
}
