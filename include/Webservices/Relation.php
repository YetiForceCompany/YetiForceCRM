<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

function GetRelatedList($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='',$skipActions=false) {
	return array( 'query' => $query , 'entries' => array() );
}

/**
 * Function that returns Activity History Query
 * @return <String>
 */
function GetHistory($parentmodule,$query,$id){
    return array('query' => $query);
}
?>
