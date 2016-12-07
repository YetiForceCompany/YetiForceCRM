<?php

/**
 * Inventory Comment Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Comment_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Comment';
	protected $defaultLabel = 'LBL_COMMENT';
	protected $colSpan = 0;
	protected $columnName = 'comment';
	protected $dbType = 'text';
	protected $onlyOne = false;
	protected $blocks = [2];

}
