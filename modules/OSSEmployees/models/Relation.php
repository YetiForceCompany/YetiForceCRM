<?php

/**
 * Relation Model
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSEmployees_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Get time control
	 */
	public function getOsstimecontrol()
	{
		$this->getQueryGenerator()->addNativeCondition(['vtiger_crmentity.smownerid' => $this->get('parentRecord')->get('assigned_user_id')]);
	}
}
