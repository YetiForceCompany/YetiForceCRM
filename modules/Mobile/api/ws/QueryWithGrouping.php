<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/Query.php';

include_once 'include/Webservices/Query.php';

class Mobile_WS_QueryWithGrouping extends Mobile_WS_Query {
	
	private $queryModule;
	
	function processQueryResultRecord($record, $user) {
		parent::processQueryResultRecord($record, $user);

		if ($this->cachedDescribeInfo() === false) {
			$describeInfo = vtws_describe($this->queryModule, $user);
			$this->cacheDescribeInfo($describeInfo);
		}
		$transformedRecord = $this->transformRecordWithGrouping($record, $this->queryModule);
		// Update entity fieldnames
		$transformedRecord['labelFields'] = $this->cachedEntityFieldnames($this->queryModule);
		return $transformedRecord;
	}
	
	function process(Mobile_API_Request $request) {
		$this->queryModule = $request->get('module');
		return parent::process($request);
	}
}