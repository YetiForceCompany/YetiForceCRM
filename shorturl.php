<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once dirname(__FILE__) . '/includes/Loader.php';

vimport ('includes.runtime.EntryPoint');

Vtiger_ShortURL_Helper::handle(vtlib_purify($_REQUEST['id']));