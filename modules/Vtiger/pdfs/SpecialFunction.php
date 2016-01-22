<?php

/**
 * Abstract class for special function in Pdf
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Vtiger_SpecialFunction_Pdf
{

	public $permittedModules;

	/**
	 * Returns pdf library object
	 */
	abstract public function process($moduleName, $id, Vtiger_PDF_Model $pdf);
}
