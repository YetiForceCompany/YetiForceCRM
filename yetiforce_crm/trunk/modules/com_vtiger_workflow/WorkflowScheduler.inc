<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once ('include/events/VTEntityData.inc');
require_once ('include/QueryGenerator/QueryGenerator.php');
require_once ('include/Zend/Json.php');
require_once ('include/utils/utils.php');
require_once ('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once ('modules/com_vtiger_workflow/VTWorkflowManager.inc');
require_once ('modules/com_vtiger_workflow/VTTaskManager.inc');
require_once ('modules/com_vtiger_workflow/VTTaskQueue.inc');
require_once ('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once ('include/Webservices/WebserviceField.php');
require_once ('include/Webservices/EntityMeta.php');
require_once ('include/Webservices/VtigerWebserviceObject.php');
require_once ('include/Webservices/VtigerCRMObject.php');
require_once ('include/Webservices/VtigerCRMObjectMeta.php');
require_once ('include/Webservices/DataTransform.php');
require_once ('include/Webservices/Retrieve.php');