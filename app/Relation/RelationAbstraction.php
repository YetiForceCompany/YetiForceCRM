<?php
/**
 * Relation abstraction class file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Relation;

/**
 * Relation abstraction class.
 */
abstract class RelationAbstraction implements RelationInterface
{
	/**
	 * Relation model instance.
	 *
	 * @var \Vtiger_Relation_Model Relation model instance by reference
	 */
	public $relationModel;
}
