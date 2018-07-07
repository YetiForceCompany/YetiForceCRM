<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ModComments_CommentsModel
{
	private $data;
	public static $ownerNamesCache = [];

	public function __construct($datarow)
	{
		$this->data = $datarow;
	}

	public function author()
	{
		$authorid = $this->data['smcreatorid'];
		if (!isset(self::$ownerNamesCache[$authorid])) {
			self::$ownerNamesCache[$authorid] = \App\Fields\Owner::getLabel($authorid);
		}
		return self::$ownerNamesCache[$authorid];
	}

	public function timestamp()
	{
		$date = new DateTimeField($this->data['modifiedtime']);

		return $date->getDisplayDateTimeValue();
	}

	public function content()
	{
		return App\Purifier::decodeHtml($this->data['commentcontent']);
	}
}
