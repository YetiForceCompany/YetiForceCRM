<?php

/**
 * Supplies Currency Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Currency_Field extends Supplies_Basic_Field
{

	protected $name = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnname = 'currency';
	protected $dbType = 'int(10)';
}
