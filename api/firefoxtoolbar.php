<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

/**
 * URL Verfication - Required to overcome Apache mis-configuration and leading to shared setup mode.
 */
require_once 'config/config.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/main/WebUI.php';
require_once('libraries/nusoap/nusoap.php');

$log = &LoggerManager::getLogger('firefoxlog');

$NAMESPACE = 'http://www.yetiforce.com';
$server = new soap_server;
$accessDenied = "You are not authorized for performing this action";
$server->configureWSDL('vtigersoap');

$server->register(
	'create_lead_from_webform',
	array('username'=>'xsd:string',
       		'session'=>'xsd:string',	
		'lastname'=>'xsd:string',
		'firstname'=>'xsd:string',
		'email'=>'xsd:string', 
		'phone'=>'xsd:string', 
		'company'=>'xsd:string', 
		'country'=>'xsd:string', 
		'description'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);




$server->register(
	'create_site_from_webform',
	array('username'=>'xsd:string', 
       		'session'=>'xsd:string',	
		'portalname'=>'xsd:string',
		'portalurl'=>'xsd:string'), 
	array('return'=>'xsd:string'),
	$NAMESPACE);



$server->register(
	'create_rss_from_webform',
	array('username'=>'xsd:string', 
       		'session'=>'xsd:string',	
		'rssurl'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);




	
$server->register(
   'create_contacts',
    array('user_name'=>'xsd:string','session'=>'xsd:string','firstname'=>'xsd:string','lastname'=>'xsd:string','phone'=>'xsd:string','mobile'=>'xsd:string','email'=>'xsd:string','street'=>'xsd:string','city'=>'xsd:string','state'=>'xsd:string','country'=>'xsd:string','zipcode'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);



$server->register(
	'create_account',
    array('username'=>'xsd:string','session'=>'xsd:string','accountname'=>'xsd:string', 'email'=>'xsd:string', 'phone'=>'xsd:string','$primary_address_street'=>'xsd:string','$primary_address_city'=>'xsd:string','$primary_address_state'=>'xsd:string','$primary_address_postalcode'=>'xsd:string','$primary_address_country'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

    
    $server->register(
	'create_ticket_from_toolbar',
	array('username'=>'xsd:string','session'=>'xsd:string', 'title'=>'xsd:string','description'=>'xsd:string','priority'=>'xsd:string','severity'=>'xsd:string','category'=>'xsd:string','user_name'=>'xsd:string','parent_id'=>'xsd:string','product_id'=>'xsd:string'),
	array('return'=>'xsd:string'),
	$NAMESPACE);
 

$server->register(
	'create_vendor_from_webform',
	array('username'=>'xsd:string',
		'session'=>'xsd:string',
       		'vendorname'=>'xsd:string',
		'email'=>'xsd:string', 
		'phone'=>'xsd:string', 
		'website'=>'xsd:string'), 
	array('return'=>'xsd:string'),
	$NAMESPACE);


$server->register(
	'create_product_from_webform',
	array('username'=>'xsd:string', 
		'session'=>'xsd:string',
		'productname'=>'xsd:string',
		'productcode'=>'xsd:string', 
		'website'=>'xsd:string'), 
	array('return'=>'xsd:string'),
	$NAMESPACE);


$server->register(
	'create_note_from_webform',
	array('username'=>'xsd:string', 
		'session'=>'xsd:string',
		'title'=>'xsd:string',
		'notecontent'=>'xsd:string'), 
	array('return'=>'xsd:string'),
	$NAMESPACE);

$server->register(
    'LogintoVtigerCRM',
    array('user_name'=>'xsd:string','password'=>'xsd:string','version'=>'xsd:string'),
    array('return'=>'tns:logindetails'),
    $NAMESPACE);
    
$server->register(
    'CheckLeadPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckContactPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);
    
$server->register(
    'CheckAccountPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckTicketPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckVendorPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckProductPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE); 

$server->register(
    'CheckNotePermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckSitePermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
    'CheckRssPermission',
    array('username'=>'xsd:string','session'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);

$server->register(
	'GetPicklistValues',
	array('username'=>'xsd:string','session'=>'xsd:string'),
	array('return'=>'tns:combo_values_array'),
	$NAMESPACE);
    
$server->wsdl->addComplexType(
        'combo_values_array',
        'complexType',
        'array',
        '',
        array(
                'productid' => array('name'=>'productid','type'=>'tns:xsd:string'),
                'productname' => array('name'=>'productname','type'=>'tns:xsd:string'),
                'ticketpriorities' => array('name'=>'ticketpriorities','type'=>'tns:xsd:string'),
                'ticketseverities' => array('name'=>'ticketseverities','type'=>'tns:xsd:string'),
                'ticketcategories' => array('name'=>'ticketcategories','type'=>'tns:xsd:string'),
                'moduleslist' => array('name'=>'moduleslist','type'=>'tns:xsd:string'),
             )
     );
$server->wsdl->addComplexType(
      'logindetails',
      'complexType',
      'array',
      '',
      array(
                'return'=>'returnVal','type'=>'tns:xsd:string',
		'session'=>'sessionId','type'=>'tns:xsd:string',
	)
);
function CheckLeadPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Leads","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckContactPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Contacts","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckAccountPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Accounts","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckTicketPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("HelpDesk","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckVendorPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Vendors","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckProductPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Products","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckNotePermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Documents","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckSitePermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Portal","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

function CheckRssPermission($username,$sessionid)
{
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("Rss","EditView") == "yes")
	{
		return "allowed";
	}else
	{
		return "denied";
	}
}

    
function create_site_from_webform($username,$sessionid,$portalname,$portalurl)
{
	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	require_once("modules/Portal/Portal.php");
	if(isPermitted("Portals","EditView") == "yes")
	{
		$result = SavePortal($portalname,$portalurl);

		$adb->println("Create New Portal from Web Form - Ends");

		if($result != '')
		  return 'URL added successfully';
		else
		  return "Portal creation failed. Try again";
	}
	else
	{
		return $accessDenied;
	}
}
function LogintoVtigerCRM($user_name,$password,$version)
{
	$adb = PearDatabase::getInstance(); $log = vglobal('log');
	require_once('modules/Users/Users.php');
	include('config/version.php');
	if($version != $YetiForce_current_version)
	{
		return array("VERSION",'00');
	}
	$return_access = array("FALSES",'00');
	
	$objuser = new Users();
	
	if($password != "")
	{
		$objuser->column_fields['user_name'] = $user_name;
		$objuser->load_user($password);
		if($objuser->is_authenticated())
		{
			$userid =  $objuser->retrieve_user_id($user_name);
			$sessionid = makeRandomPassword();
			unsetServerSessionId($userid);
			$sql="insert into vtiger_soapservice values(?,?,?)";
			$result = $adb->pquery($sql, array($userid,'FireFox' ,$sessionid));
			$return_access = array("TRUES",$sessionid);
		}else
		{
			$return_access = array("FALSES",'00');
		}
	}else
	{
			//$server->setError("Invalid username and/or password");
			$return_access = array("FALSES",'00');
	}
	$objuser = $objuser;
	return $return_access;
}

function create_rss_from_webform($username,$sessionid,$url)
{

	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	require_once("modules/Rss/Rss.php");

	$oRss = new vtigerRSS();
	if(isPermitted("RSS","EditView") == "yes")
	{
		if($oRss->setRSSUrl($url))
		{
			if($oRss->saveRSSUrl($url) == false)
			{
				return "RSS feed addition failed. Try again";
			}
			else
			{
					return 'RSS feed added successfully.';
			}

	  }else
	  {
	     return "Not a valid RSS Feed or your Proxy Settings is not correct. Try again";
    }
	}
	else
	{
		return $accessDenied;
	}

}


function create_note_from_webform($username,$sessionid,$subject,$desc)
{
	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	$adb->println("Create New Document from Web Form - Starts");
	require_once("modules/Documents/Documents.php");

	$focus = new Documents();
	if(isPermitted("Documents","EditView") == "yes")
	{
		$focus->column_fields['notes_title'] = $subject;
		$focus->column_fields['notecontent'] = $desc;

		$focus->save("Documents");

		$focus->retrieve_entity_info($focus->id,"Documents");

		$adb->println("Create New Document from Web Form - Ends");

		if($focus->id != '')
		return 'Document added successfully.';
		else
		return "Document creation failed. Try again";
	}
	else
	{
		return $accessDenied;
	}

}

function create_product_from_webform($username,$sessionid,$productname,$code,$website)
{
	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	$adb->println("Create New Product from Web Form - Starts");
	
  require_once("modules/Products/Products.php");
	if(isPermitted("Products","EditView") == "yes")
	{
		$focus = new Products();
		$focus->column_fields['productname'] = $productname;
		$focus->column_fields['productcode'] = $code;
		$focus->column_fields['website'] = $website;
		$focus->column_fields['assigned_user_id'] = $user_id;
		$focus->column_fields['discontinued'] = "1";

		$focus->save("Products");
		$adb->println("Create New Product from Web Form - Ends");

		if($focus->id != '')
		  return 'Product added successfully.';
		else
		  return "Product creation failed. Try again";
	}
	else
	{
		return $accessDenied;
	}

	
}

function create_vendor_from_webform($username,$sessionid,$vendorname,$email,$phone,$website)
{
	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	$adb->println("Create New Vendor from Web Form - Starts");
	require_once("modules/Vendors/Vendors.php");
	if(isPermitted("Vendors","EditView" ) == "yes")
	{
		$focus = new Vendors();
		$focus->column_fields['vendorname'] = $vendorname;
		$focus->column_fields['email'] = $email;
		$focus->column_fields['phone'] = $phone;
		$focus->column_fields['website'] = $website;

		$focus->save("Vendors");

		$focus->retrieve_entity_info($focus->id,"Vendors");

		$adb->println("Create New Vendor from Web Form - Ends");

		if($focus->id != '')
		return 'Vendor added successfully';
		else
		return "Vendor creation failed. Try again";
  }		
  else
	{
		return $accessDenied;
	}

	
}

function create_ticket_from_toolbar($username,$sessionid,$title,$description,$priority,$severity,$category,$user_name,$parent_id,$product_id)
{
	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');

	if(isPermitted("HelpDesk","EditView") == "yes")
	{

		$seed_ticket = new HelpDesk();
		$output_list = Array();

		require_once('modules/HelpDesk/HelpDesk.php');
		$ticket = new HelpDesk();

		$ticket->column_fields[ticket_title] = $title;
		$ticket->column_fields[description]=$description;
		$ticket->column_fields[ticketpriorities]=$priority;
		$ticket->column_fields[ticketseverities]=$severity;
		$ticket->column_fields[ticketcategories]=$category;
		$ticket->column_fields[ticketstatus]='Open';

		$ticket->column_fields[parent_id]=$parent_id;
		$ticket->column_fields[product_id]=$product_id;
		$ticket->column_fields[assigned_user_id]=$user_id;
		//$ticket->saveentity("HelpDesk");
		$ticket->save("HelpDesk");

		if($ticket->id != '')
      return "Ticket created successfully";
    else
      return "Error while creating Ticket.Try again";  
	}
	else
	{
		return $accessDenied;
	}


}

function create_account($username,$sessionid,$accountname,$email,$phone,$primary_address_street,$primary_address_city,$primary_address_state,$primary_address_postalcode,$primary_address_country)
{
	if(!validateSession($username,$sessionid))
	return null;
	global $current_user,$log,$adb;
	$log->DEBUG("Entering with data ".$username.$accountname.$email.$phone."<br>".$primary_address_street.$primary_address_city.$primary_address_state.$primary_address_postalcode.$primary_address_country);
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id,'Users');
	require_once("modules/Accounts/Accounts.php");
	if(isPermitted("Accounts","EditView") == "yes")
	{
		$query = "SELECT accountname FROM vtiger_account,vtiger_crmentity WHERE accountname =? and vtiger_account.accountid = vtiger_crmentity.crmid and vtiger_crmentity.deleted != 1";
		$result = $adb->pquery($query, array($accountname));
	        if($adb->num_rows($result) > 0)
		{
			return "Accounts";
			die;
		}
		$account=new Accounts();
		$account->column_fields['accountname']=$accountname;
		$account->column_fields['email1']=$email;
		$account->column_fields['phone']=$phone;
		$account->column_fields['bill_street']=$primary_address_street;
		$account->column_fields['bill_city']=$primary_address_city;
		$account->column_fields['bill_state']=$primary_address_state;
		$account->column_fields['bill_code']=$primary_address_postalcode;
		$account->column_fields['bill_country']=$primary_address_country;
		$account->column_fields['ship_street']=$primary_address_street;
		$account->column_fields['ship_city']=$primary_address_city;
		$account->column_fields['ship_state']=$primary_address_state;
		$account->column_fields['ship_code']=$primary_address_postalcode;
		$account->column_fields['ship_country']=$primary_address_country;
		$account->column_fields['assigned_user_id']=$user_id;
		$account->save('Accounts');
		if($account->id != '')
      return "Success";
    else
      return "Error while adding Account.Try again";  
	}
	else
	{
		return $accessDenied;
	}

}

function create_lead_from_webform($username,$sessionid,$lastname,$email,$phone,$company,$country,$description,$firstname)
{

	$log = vglobal('log');
	$adb = PearDatabase::getInstance();
	$current_user  = vglobal('current_user');
	if(!validateSession($username,$sessionid))
	return null;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	$adb->println("Create New Lead from Web Form - Starts");
	require_once("modules/Leads/Leads.php");

	$focus = new Leads();
	if(isPermitted("Leads","EditView") == "yes")
	{
		$focus->column_fields['lastname'] = $lastname;
		$focus->column_fields['firstname'] = $firstname;
		$focus->column_fields['email'] = $email;
		$focus->column_fields['phone'] = $phone;
		$focus->column_fields['company'] = $company;
		$focus->column_fields['country'] = $country;
		$focus->column_fields['description'] = $description;
		$focus->column_fields['assigned_user_id'] = $user_id;
		$focus->save("Leads");
		$adb->println("Create New Lead from Web Form - Ends");
		if($focus->id != '')
		  return "Thank you for your interest. Information has been successfully added as Lead.";
		else
		  return "Lead creation failed. Try again";
  }
	else
	{
		return $accessDenied;
	}


}

function create_contacts($user_name,$sessionid,$firstname,$lastname,$phone,$mobile,$email,$street,$city,$state,$country,$zipcode)
{
	$log = vglobal('log');
	$log->DEBUG("Entering into create_contacts");
	$birthdate = "";
	if(!validateSession($user_name,$sessionid))
	return null;

	return create_contact1($user_name, $firstname, $lastname, $email,"", "","", $mobile, "",$street,$city,$state,$zipcode,$country,$city,$street,$state,$zipcode,$country,$phone,"","","","",$birthdate,"","");
	
}

function create_contact1($user_name, $first_name, $last_name, $email_address ,$account_name , $salutation , $title, $phone_mobile, $reports_to,$primary_address_street,$primary_address_city,$primary_address_state,$primary_address_postalcode,$primary_address_country,$alt_address_city,$alt_address_street,$alt_address_state,$alt_address_postalcode,$alt_address_country,$office_phone,$home_phone,$other_phone,$fax,$department,$birthdate,$assistant_name,$assistant_phone,$description='')
{
	$adb = PearDatabase::getInstance();
	$log = vglobal('log');
	$current_user  = vglobal('current_user');
	require_once('modules/Users/Users.php');
	$seed_user = new Users();
	$user_id = $seed_user->retrieve_user_id($user_name);
	$current_user = $seed_user;
	$current_user->retrieve_entity_info($user_id,'Users');

	require_once('modules/Contacts/Contacts.php');
  if(isPermitted("Contacts","EditView") == "yes")
  {
   $contact = new Contacts();
   $contact->column_fields[firstname]= $first_name;
   $contact->column_fields[lastname]= $last_name;
   //$contact->column_fields[account_id]=retrieve_account_id($account_name,$user_id);// NULL value is not supported NEED TO FIX
   $contact->column_fields[salutation]=$salutation;
   // EMAIL IS NOT ADDED
   $contact->column_fields[title]=$title;
   $contact->column_fields[email]=$email_address;
   $contact->column_fields[mobile]=$phone_mobile;
   //$contact->column_fields[reports_to_id] =retrievereportsto($reports_to,$user_id,$account_id);// NOT FIXED IN SAVEENTITY.PHP
   $contact->column_fields[mailingstreet]=$primary_address_street;
   $contact->column_fields[mailingcity]=$primary_address_city;
   $contact->column_fields[mailingcountry]=$primary_address_country;
   $contact->column_fields[mailingstate]=$primary_address_state;
   $contact->column_fields[mailingzip]=$primary_address_postalcode;
   $contact->column_fields[otherstreet]=$alt_address_street;
   $contact->column_fields[othercity]=$alt_address_city;
   $contact->column_fields[othercountry]=$alt_address_country;
   $contact->column_fields[otherstate]=$alt_address_state;
   $contact->column_fields[otherzip]=$alt_address_postalcode;
   $contact->column_fields[assigned_user_id]=$user_id;
   // new Fields
   $contact->column_fields[phone]= $office_phone;
   $contact->column_fields[homephone]= $home_phone;
   $contact->column_fields[otherphone]= $other_phone;
   $contact->column_fields[fax]= $fax;
   $contact->column_fields[department]=$department;
   $contact->column_fields[birthday]= DateTimeField::convertToUserFormat($birthdate);
   $contact->column_fields[assistant]= $assistant_name;
   $contact->column_fields[assistantphone]= $assistant_phone;
   $contact->column_fields[description]= $description;
   $contact->save("Contacts");
   if($contact->id != '')
      return 'Contact added successfully';
   else
      return "Contact creation failed. Try again";
  }
	else
	{
		return $accessDenied;
	}

}
function GetPicklistValues($username,$sessionid,$tablename)
{
	global $current_user,$log,$adb;
	if(!validateSession($username,$sessionid))
	return null;

	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id,'Users');
	require_once("include/utils/UserInfoUtil.php");
	$roleid = fetchUserRole($user_id);
	checkFileAccessForInclusion('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
	{
		$query = "select " . $adb->sql_escape_string($tablename) . " from vtiger_". $adb->sql_escape_string($tablename);		
			$result1 = $adb->pquery($query, array());
		for($i=0;$i<$adb->num_rows($result1);$i++)
		{
			$output[$i] = decode_html($adb->query_result($result1,$i,$tablename));
		}			
	}
	else if((isPermitted("HelpDesk","EditView") == "yes") && (CheckFieldPermission($tablename,'HelpDesk') == 'true'))
	{
		$query = "select " .$adb->sql_escape_string($tablename) . " from vtiger_". $adb->sql_escape_string($tablename) ." inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_". $adb->sql_escape_string($tablename) .".picklist_valueid where roleid=? and picklistid in (select picklistid from vtiger_". $adb->sql_escape_string($tablename)." ) order by sortid";	
		$result1 = $adb->pquery($query, array($roleid));
		for($i=0;$i<$adb->num_rows($result1);$i++)
		{
			$output[$i] = decode_html($adb->query_result($result1,$i,$tablename));
		}			
	}
	else
	{
		$output[] = 'Not Accessible';
	}
		
	return $output;
}
function unsetServerSessionId($id)
{
	$adb = PearDatabase::getInstance();
	$adb->println("Inside the function unsetServerSessionId");

	$id = (int) $id;

	$adb->query("delete from vtiger_soapservice where type='FireFox' and id=$id");

	return;
}
function validateSession($username, $sessionid)
{
	global $adb;
	$adb->println("Inside function validateSession($username, $sessionid)");
	require_once("modules/Users/Users.php");
	$seed_user = new Users();
	$id = $seed_user->retrieve_user_id($username);

	$server_sessionid = getServerSessionId($id);

	$adb->println("Checking Server session id and customer input session id ==> $server_sessionid == $sessionid");

	if($server_sessionid == $sessionid)
	{
		$adb->println("Session id match. Authenticated to do the current operation.");
		return true;
	}
	else
	{
		$adb->println("Session id does not match. Not authenticated to do the current operation.");
		return false;
	}
}
function getServerSessionId($id)
{
	$adb = PearDatabase::getInstance();
	$adb->println("Inside the function getServerSessionId($id)");

	//To avoid SQL injection we are type casting as well as bound the id variable. In each and every function we will call this function
	$id = (int) $id;

	$query = "select * from vtiger_soapservice where type='FireFox' and id={$id}";
	$sessionid = $adb->query_result($adb->query($query),0,'sessionid');

	return $sessionid;
}
/* Begin the HTTP listener service and exit. */ 
if (!isset($HTTP_RAW_POST_DATA)){
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');
}
$server->service($HTTP_RAW_POST_DATA); 
exit(); 
?>
