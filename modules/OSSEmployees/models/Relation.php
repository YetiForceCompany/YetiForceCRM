<?php

/**
 * Relation Model
 * @package YetiForce.Model
 * @license licenses/License.html
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
