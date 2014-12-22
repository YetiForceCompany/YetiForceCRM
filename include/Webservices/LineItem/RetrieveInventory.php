<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'include/Webservices/Retrieve.php';

/**
 * Retrieve inventory record with LineItems
 */
function vtws_retrieve_inventory($id){
	global $current_user;

	$record = vtws_retrieve($id, $current_user);

	$handler = vtws_getModuleHandlerFromName('LineItem', $user);
    $id = vtws_getIdComponents($id);
    $id = $id[1];
	$inventoryLineItems = $handler->getAllLineItemForParent($id);

	$record['LineItems'] = $inventoryLineItems;

	return $record;
}

?>
