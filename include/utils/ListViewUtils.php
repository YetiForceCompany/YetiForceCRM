<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header: /cvsroot/vtigercrm/vtiger_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

require_once('include/database/PearDatabase.php');
require_once('include/utils/CommonUtils.php'); //new
require_once('user_privileges/default_module_view.php'); //new
require_once('include/utils/UserInfoUtil.php');

/** Function to get the list query for a module
 * @param $module -- module name:: Type string
 * @param $where -- where:: Type string
 * @returns $query -- query:: Type query
 */
function getListQuery($module, $where = '')
{

	\App\Log::trace("Entering getListQuery(" . $module . "," . $where . ") method ...");

	$current_user = vglobal('current_user');
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' =>
			'vtiger_users.last_name'), 'Users');
	switch ($module) {
		Case "HelpDesk":
			$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_troubletickets.title, vtiger_troubletickets.status,
			vtiger_troubletickets.priority, vtiger_troubletickets.parent_id,
			vtiger_contactdetails.contactid, vtiger_contactdetails.firstname,
			vtiger_contactdetails.lastname, vtiger_account.accountid,
			vtiger_account.accountname, vtiger_ticketcf.*, vtiger_troubletickets.ticket_no
			FROM vtiger_troubletickets
			INNER JOIN vtiger_ticketcf
				ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_contactdetails
				ON vtiger_troubletickets.parent_id = vtiger_contactdetails.contactid
			LEFT JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_troubletickets.parent_id
			LEFT JOIN vtiger_users
				ON vtiger_crmentity.smownerid = vtiger_users.id
			LEFT JOIN vtiger_products
				ON vtiger_products.productid = vtiger_troubletickets.product_id %s 
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;

		Case "Accounts":
			//Query modified to sort by assigned to
			$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_account.*, vtiger_accountaddress.*, vtiger_accountscf.*
			FROM vtiger_account
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_account.accountid
			INNER JOIN vtiger_accountaddress
				ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid
			INNER JOIN vtiger_accountscf
				ON vtiger_account.accountid = vtiger_accountscf.accountid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_account vtiger_account2
				ON vtiger_account.parentid = vtiger_account2.accountid %s 
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case "Leads":
			$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
			vtiger_leaddetails.company, vtiger_leadaddress.phone,
			vtiger_leadsubdetails.website, vtiger_leaddetails.email,
			vtiger_leadscf.*
			FROM vtiger_leaddetails
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadsubdetails
				ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
			INNER JOIN vtiger_leadaddress
				ON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid
			INNER JOIN vtiger_leadscf
				ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid %s 
			WHERE vtiger_crmentity.deleted = 0 && vtiger_leaddetails.converted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case 'Products':
			$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.description, vtiger_products.*, vtiger_productcf.*
			FROM vtiger_products
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			INNER JOIN vtiger_productcf
				ON vtiger_products.productid = vtiger_productcf.productid
			LEFT JOIN vtiger_vendor
				ON vtiger_vendor.vendorid = vtiger_products.vendor_id
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid %s WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case 'Documents':
			$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,vtiger_crmentity.crmid, vtiger_crmentity.modifiedtime,
			vtiger_crmentity.smownerid,`vtiger_trees_templates_data`.*,vtiger_notes.*
			FROM vtiger_notes
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_notes.notesid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN `vtiger_trees_templates_data`
				ON vtiger_notes.folderid = `vtiger_trees_templates_data`.tree %s 
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case "Contacts":
			//Query modified to sort by assigned to
			$query = "SELECT vtiger_contactdetails.firstname, vtiger_contactdetails.lastname,
			vtiger_contactdetails.title, vtiger_contactdetails.parentid,
			vtiger_contactdetails.email, vtiger_contactdetails.phone,
			vtiger_crmentity.smownerid, vtiger_crmentity.crmid
			FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactsubdetails
				ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactscf
				ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid
			LEFT JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_contactdetails.parentid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_contactdetails vtiger_contactdetails2
				ON vtiger_contactdetails.reportsto = vtiger_contactdetails2.contactid
			LEFT JOIN vtiger_customerdetails
				ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid";
			if (\App\Request::_get('from_dashboard') === true && \App\Request::_get('type') == 'dbrd') {
				$query .= " INNER JOIN vtiger_campaign_records on vtiger_campaign_records.crmid = " .
					"vtiger_contactdetails.contactid";
			}

			$query .= " %s WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case 'Calendar':

			$query = "SELECT vtiger_activity.activityid as act_id,vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype,
		vtiger_activity.*
		FROM vtiger_activity
		LEFT JOIN vtiger_activitycf
			ON vtiger_activitycf.activityid = vtiger_activity.activityidd
		LEFT OUTER JOIN vtiger_activity_reminder
			ON vtiger_activity_reminder.activity_id = vtiger_activity.activityid
		LEFT JOIN vtiger_crmentity
			ON vtiger_crmentity.crmid = vtiger_activity.activityid
		LEFT JOIN vtiger_users
			ON vtiger_users.id = vtiger_crmentity.smownerid
		LEFT JOIN vtiger_groups
			ON vtiger_groups.groupid = vtiger_crmentity.smownerid
		LEFT JOIN vtiger_users vtiger_users2
			ON vtiger_crmentity.modifiedby = vtiger_users2.id
		LEFT JOIN vtiger_groups vtiger_groups2
			ON vtiger_crmentity.modifiedby = vtiger_groups2.groupid";

			//end
			$query .= \App\PrivilegeQuery::getAccessConditions($module, $current_user->id);
			$query .= ' ' . $where;
			break;
		Case "Faq":
			$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime,
			vtiger_faq.*
			FROM vtiger_faq
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_faq.id
			LEFT JOIN vtiger_products
				ON vtiger_faq.product_id = vtiger_products.productid %s
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;

		Case "Vendors":
			$query = "SELECT vtiger_crmentity.crmid, vtiger_vendor.*, vtiger_vendoraddress.*
			FROM vtiger_vendor
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_vendor.vendorid
			INNER JOIN vtiger_vendoraddress
				ON vtiger_vendor.vendorid = vtiger_vendoraddress.vendorid
			INNER JOIN vtiger_vendorcf
				ON vtiger_vendor.vendorid = vtiger_vendorcf.vendorid
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, $where);
			break;
		Case "PriceBooks":
			$query = "SELECT vtiger_crmentity.crmid, vtiger_pricebook.*, vtiger_currency_info.currency_name
			FROM vtiger_pricebook
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_pricebook.pricebookid
			INNER JOIN vtiger_pricebookcf
				ON vtiger_pricebook.pricebookid = vtiger_pricebookcf.pricebookid
			LEFT JOIN vtiger_currency_info
				ON vtiger_pricebook.currency_id = vtiger_currency_info.id
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, $where);
			break;
		Case "Campaigns":
			//Query modified to sort by assigned to
			//query modified -Code contribute by Geoff(http://forums.vtiger.com/viewtopic.php?t=3376)
			$query = "SELECT vtiger_crmentity.*,
			vtiger_campaign.*
			FROM vtiger_campaign
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
			INNER JOIN vtiger_campaignscf
			        ON vtiger_campaign.campaignid = vtiger_campaignscf.campaignid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_products
				ON vtiger_products.productid = vtiger_campaign.product_id %s 
			WHERE vtiger_crmentity.deleted = 0 %s";
			$query = sprintf($query, getNonAdminAccessControlQuery($module, $current_user), $where);
			break;
		Case "Users":
			$query = "SELECT id,user_name,first_name,last_name,email1,is_admin,status,
					vtiger_user2role.roleid as roleid,vtiger_role.depth as depth
				 	FROM vtiger_users
				 	INNER JOIN vtiger_user2role ON vtiger_users.id = vtiger_user2role.userid
				 	INNER JOIN vtiger_role ON vtiger_user2role.roleid = vtiger_role.roleid
					WHERE deleted=0 && status <> 'Inactive' %s";
			$query = sprintf($query, $where);
			break;
		Case "Reservations":
			$query = "SELECT vtiger_crmentity.*, vtiger_reservations.*, vtiger_reservationscf.* FROM vtiger_reservations 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_reservations.reservationsid 
                INNER JOIN vtiger_reservationscf ON vtiger_reservationscf.reservationsid = vtiger_reservations.reservationsid 
                LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
                LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid 
                LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_reservations.relatedida 
                LEFT JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_reservations.relatedida 
                LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_reservations.relatedida 
                LEFT JOIN vtiger_project ON vtiger_project.projectid = vtiger_reservations.relatedidb 
                LEFT JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_reservations.relatedidb 
                WHERE vtiger_reservations.reservationsid > 0 
                && vtiger_crmentity.deleted = 0 ";
			$query = sprintf($query, $where);
			break;
		default:
			// vtlib customization: Include the module file
			$focus = CRMEntity::getInstance($module);
			$query = $focus->getListQuery($module, $where);
		// END
	}

	if ($module !== 'Users') {
		$instance = CRMEntity::getInstance($module);
		$query = $instance->listQueryNonAdminChange($query);
	}
	\App\Log::trace('Exiting getListQuery method ...');
	return $query;
}

function popup_decode_html($str)
{
	$defaultCharset = AppConfig::main('default_charset');
	$slashes_str = \vtlib\Functions::fromHtmlPopup($str);
	$slashes_str = htmlspecialchars($slashes_str, ENT_QUOTES, $defaultCharset);
	return \App\Purifier::decodeHtml(\vtlib\Functions::br2nl($slashes_str));
}
