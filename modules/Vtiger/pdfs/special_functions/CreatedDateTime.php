<?php

/**
 * Special function displaying creation date time of the record
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Pdf_CreatedDateTime extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['all'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT `createdtime` FROM `vtiger_crmentity` WHERE `crmid` = ? LIMIT 1;';
		$result = $db->pquery($query, [$id]);
		$createdTime = $db->getSingleValue($result);

		return DateTimeField::convertToUserFormat($createdTime);
	}
}
