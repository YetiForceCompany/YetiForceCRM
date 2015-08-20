<?php

/**
 * Supplies Comment Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Comment_Field extends Supplies_Basic_Field
{

	protected $name = 'Comment';
	protected $defaultLabel = 'LBL_COMMENT';
	protected $columnname = 'comment';
	protected $dbType = 'varchar(500)';

}
