<?php 
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
 function get_query( $tablename, $tableid )
 {
 $query = "SELECT $tablename.$tableid,case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name 
                                FROM $tablename
                                inner join vtiger_crmentity on vtiger_crmentity.crmid=$tablename.$tableid
                                LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id and vtiger_users.status='Active' 
								LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
								WHERE vtiger_crmentity.deleted <> '1'";
return $query;
}							
?>