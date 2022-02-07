<?php

/**
 * Inventory Subunit Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Davide Alghi <davide@penguinable.it>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Subunit_InventoryField extends Vtiger_Unit_InventoryField
{
	/** {@inheritdoc} */
	protected $type = 'Subunit';
	/** {@inheritdoc} */
	protected $defaultLabel = 'FL_SUBUNIT';
	/** {@inheritdoc} */
	protected $columnName = 'subunit';
	/** {@inheritdoc} */
	protected $dbType = 'string';
	/** {@inheritdoc} */
	protected $onlyOne = true;
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::TEXT;
}
