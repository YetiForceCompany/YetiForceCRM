<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ******************************************************************************* */

class RelatedModuleMeta
{

	private $module;
	private $relatedModule;
	private $CAMPAIGNCONTACTREL = 1;

	private function __construct($module, $relatedModule)
	{
		$this->module = $module;
		$this->relatedModule = $relatedModule;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $relatedModule
	 * @return RelatedModuleMeta 
	 */
	public static function getInstance($module, $relatedModule)
	{
		return new RelatedModuleMeta($module, $relatedModule);
	}

	public function getRelationMeta()
	{
		$campaignContactRel = array('Campaigns', 'Contacts');
		if (in_array($this->module, $campaignContactRel) && in_array($this->relatedModule, $campaignContactRel)) {
			return $this->getRelationMetaInfo($this->CAMPAIGNCONTACTREL);
		}
	}

	private function getRelationMetaInfo($relationId)
	{
		return [
			'relationTable' => 'vtiger_campaign_records',
			'Campaigns' => 'campaignid',
			'Contacts' => 'crmid'
		];
	}
}
