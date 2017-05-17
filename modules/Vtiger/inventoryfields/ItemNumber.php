<?php

/**
 * Inventory Item Number Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ItemNumber_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'ItemNumber';
	protected $defaultLabel = 'LBL_ITEM_NUMBER';
	protected $columnName = 'seq';

}
