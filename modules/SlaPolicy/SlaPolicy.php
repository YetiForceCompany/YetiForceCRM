<?php
/**
 * SlaPolicy class.
 *
 * @package   Module
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class SlaPolicy
{
	public $table_name = 'u_#__servicecontracts_sla_policy';
	public $table_index = 'id';
	public $tab_name = ['u_#__servicecontracts_sla_policy'];
	public $tab_name_index = [
		'u_#__servicecontracts_sla_policy' => 'id',
	];
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_OPERATIONAL_HOURS' => ['u_#__servicecontracts_sla_policy', 'operational_hours'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_OPERATIONAL_HOURS' => 'operational_hours',
	];
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
	];
	public $customFieldTable = [];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
}
