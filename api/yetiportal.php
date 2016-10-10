<?php
/* {[The file is published on the basis of YetiForce Public License
 * that can be found in the following directory: licenses/License.html]} */

require('include/ConfigUtils.php');
if (!in_array('yetiportal', $enabledServices)) {
	$apiLog = new \Exception\NoPermittedToApi();
	$apiLog->stop(['status' => 0, 'message' => 'YetiPortal - Service is not active']);
}

require('include/main/WebUI.php');
require('libraries/nusoap/nusoap.php');
require('modules/HelpDesk/HelpDesk.php');
require('modules/Emails/mail.php');
require('modules/Settings/CustomerPortal/helpers/CustomerPortalPassword.php');

AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/yetiportal.log');

/** Configure language for server response translation */
$current_language = vglobal('current_language');
if (!isset($current_language))
	$current_language = vglobal('default_language');
$log = LoggerManager::getInstance('Portal');

$userid = getPortalUserid();
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

$NAMESPACE = 'http://www.yetiforce.com';
$server = new soap_server;
$server->soap_defencoding = AppConfig::main('default_charset');
$server->decode_utf8 = false;
$server->configureWSDL('customerportal');

$server->wsdl->addComplexType(
	'common_array', 'complexType', 'array', '', array(
	'fieldname' => array('name' => 'fieldname', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'common_array1', 'complexType', 'array', '', 'SOAP-ENC:Array', [], array(
	array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:common_array[]')
	), 'tns:common_array'
);

$server->wsdl->addComplexType(
	'add_contact_detail_array', 'complexType', 'array', '', array(
	'salutation' => array('name' => 'salutation', 'type' => 'xsd:string'),
	'firstname' => array('name' => 'firstname', 'type' => 'xsd:string'),
	'phone' => array('name' => 'phone', 'type' => 'xsd:string'),
	'lastname' => array('name' => 'lastname', 'type' => 'xsd:string'),
	'mobile' => array('name' => 'mobile', 'type' => 'xsd:string'),
	'parentid' => array('name' => 'parentid', 'type' => 'xsd:string'),
	'leadsource' => array('name' => 'leadsource', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'field_details_array', 'complexType', 'array', '', array(
	'fieldlabel' => array('name' => 'fieldlabel', 'type' => 'xsd:string'),
	'fieldvalue' => array('name' => 'fieldvalue', 'type' => 'xsd:string'),
	)
);
$server->wsdl->addComplexType(
	'field_datalist_array', 'complexType', 'array', '', array(
	'fielddata' => array('name' => 'fielddata', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'product_list_array', 'complexType', 'array', '', array(
	'productid' => array('name' => 'productid', 'type' => 'xsd:string'),
	'productname' => array('name' => 'productname', 'type' => 'xsd:string'),
	'productcode' => array('name' => 'productcode', 'type' => 'xsd:string'),
	'commissionrate' => array('name' => 'commissionrate', 'type' => 'xsd:string'),
	'qtyinstock' => array('name' => 'qtyinstock', 'type' => 'xsd:string'),
	'qty_per_unit' => array('name' => 'qty_per_unit', 'type' => 'xsd:string'),
	'unit_price' => array('name' => 'unit_price', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'get_ticket_attachments_array', 'complexType', 'array', '', array(
	'files' => array(
		'fileid' => 'xsd:string', 'type' => 'tns:xsd:string',
		'filename' => 'xsd:string', 'type' => 'tns:xsd:string',
		'filesize' => 'xsd:string', 'type' => 'tns:xsd:string',
		'filetype' => 'xsd:string', 'type' => 'tns:xsd:string',
		'filecontents' => 'xsd:string', 'type' => 'tns:xsd:string'
	),
	)
);


$server->register(
	'authenticate_user', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'change_password', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'create_ticket', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

//for a particular contact ticket list
$server->register(
	'get_tickets_list', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_ticket_comments', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_combo_values', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_KBase_details', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array1'), $NAMESPACE);

$server->register(
	'save_faq_comment', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'update_ticket_comment', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'close_current_ticket', array('fieldname' => 'tns:common_array'), array('return' => 'xsd:string'), $NAMESPACE);

$server->register(
	'send_mail_for_password', array('email' => 'xsd:string'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_ticket_creator', array('fieldname' => 'tns:common_array'), array('return' => 'xsd:string'), $NAMESPACE);

$server->register(
	'get_picklists', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_ticket_attachments', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_filecontent', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'add_ticket_attachment', array('fieldname' => 'tns:common_array'), array('return' => 'tns:common_array'), $NAMESPACE);

$server->register(
	'get_cf_field_details', array('id' => 'xsd:string', 'contactid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'get_check_account_id', array('id' => 'xsd:string'), array('return' => 'xsd:string'), $NAMESPACE);

//to get details of invoices and documents
$server->register(
	'get_details', array('id' => 'xsd:string', 'block' => 'xsd:string', 'contactid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

//to get the products list for the entire account of a contact
$server->register(
	'get_product_list_values', array('id' => 'xsd:string', 'block' => 'xsd:string', 'sessionid' => 'xsd:string', 'only_mine' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'get_list_values', array('id' => 'xsd:string', 'block' => 'xsd:string', 'sessionid' => 'xsd:string', 'only_mine' => 'xsd:string'), array('return' => 'tns:field_datalist_array'), $NAMESPACE);

$server->register(
	'get_product_urllist', array('customerid' => 'xsd:string', 'productid' => 'xsd:string', 'block' => 'xsd:string'), array('return' => 'tns:field_datalist_array'), $NAMESPACE);

$server->register(
	'get_filecontent_detail', array('id' => 'xsd:string', 'folderid' => 'xsd:string', 'block' => 'xsd:string', 'contactid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:get_ticket_attachments_array'), $NAMESPACE);

$server->register(
	'get_inventory_products', array('id' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'get_modules', [], array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'show_all', array('module' => 'xsd:string'), array('return' => 'xsd:string'), $NAMESPACE);

$server->register(
	'get_documents', array('id' => 'xsd:string', 'module' => 'xsd:string', 'customerid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'updateCount', array('id' => 'xsd:string'), array('return' => 'xsd:string'), $NAMESPACE);

//to get the Services list for the entire account of a contact
$server->register(
	'get_service_list_values', array('id' => 'xsd:string', 'module' => 'xsd:string', 'sessionid' => 'xsd:string', 'only_mine' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

//to get the Project Tasks for a given Project
$server->register(
	'get_project_components', array('id' => 'xsd:string', 'module' => 'xsd:string', 'customerid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

//to get the Project Tickets for a given Project
$server->register(
	'get_project_tickets', array('id' => 'xsd:string', 'module' => 'xsd:string', 'customerid' => 'xsd:string', 'sessionid' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

$server->register(
	'get_summary_widgets', array('id' => 'xsd:string', 'type' => 'xsd:string'), array('return' => 'tns:field_details_array'), $NAMESPACE);

/**
 * Helper class to provide functionality like caching etc...
 */
class Vtiger_Soap_YetiPortal
{

	/** Preference value caching */
	static $_prefs_cache = [];

	static function lookupPrefValue($key)
	{
		if (self::$_prefs_cache[$key]) {
			return self::$_prefs_cache[$key];
		}
		return false;
	}

	static function updatePrefValue($key, $value)
	{
		self::$_prefs_cache[$key] = $value;
	}

	/** Sessionid caching for re-use */
	static $_sessionid = [];

	static function lookupSessionId($key)
	{
		if (isset(self::$_sessionid[$key])) {
			return self::$_sessionid[$key];
		}
		return false;
	}

	static function updateSessionId($key, $value)
	{
		self::$_sessionid[$key] = $value;
	}

	static $_session = [];

	/** Store available module information */
	static $_modules = false;

	static function lookupAllowedModules()
	{
		return self::$_modules;
	}

	static function updateAllowedModules($modules)
	{
		self::$_modules = $modules;
	}
}

/** 	function used to get the list of ticket comments
 * @param array $input_array - array which contains the following parameters
 * int $id - customer id
 * string $sessionid - session id
 * int $ticketid - ticket id
 * @return array $response - ticket comments and details as a array with elements comments, owner and createdtime which will be returned from the function get_ticket_comments_list
 */
function get_ticket_comments($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$adb = PearDatabase::getInstance();
	$adb->println("Entering customer portal function get_ticket_comments");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if (!validateSession($id, $sessionid))
		return null;

	$userid = getPortalUserid();
	$user = new Users();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	if (isPermitted('ModComments', 'DetailView')) {
		$response = _getTicketModComments($ticketid);
	}
	return $response;
}

/**
 * Function added to get the Tickets Comments
 * @global <PearDataBase> $adb
 * @param <Integer> $ticketId
 * @return <Array>
 */
function _getTicketModComments($ticketId)
{
	$adb = PearDatabase::getInstance();
	$sql = "SELECT * FROM vtiger_modcomments
			INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid && deleted = 0
			WHERE related_to = ? ORDER BY createdtime DESC";
	$result = $adb->pquery($sql, array($ticketId));
	$rows = $adb->num_rows($result);
	$output = [];

	for ($i = 0; $i < $rows; $i++) {
		$customer = $adb->query_result($result, $i, 'customer');
		$owner = $adb->query_result($result, $i, 'smownerid');

		if (!empty($customer)) {
			$emailResult = $adb->pquery('SELECT * FROM vtiger_portalinfo WHERE id = ?', array($customer));
			$output[$i]['owner'] = $adb->query_result($emailResult, 0, 'user_name');
		} else {
			$output[$i]['owner'] = \includes\fields\Owner::getLabel($owner);
		}

		$output[$i]['comments'] = decode_html(nl2br($adb->query_result($result, $i, 'commentcontent')));
		$output[$i]['createdtime'] = $adb->query_result($result, $i, 'createdtime');
	}
	return $output;
}

/** 	function used to get the combo values ie., picklist values of the HelpDesk module and also the list of products
 * 	@param array $input_array - array which contains the following parameters
  =>	int $id - customer id
  string $sessionid - session id
 * 	return array $output - array which contains the product id, product name, ticketpriorities, ticketseverities, ticketcategories and module owners list
 */
function get_combo_values($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$adb->println("Entering customer portal function get_combo_values");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];

	if (!validateSession($id, $sessionid))
		return null;

	$output = [];
	$sql = "select  productid, productname from vtiger_products inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid where vtiger_crmentity.deleted=0";
	$result = $adb->pquery($sql, []);
	$noofrows = $adb->num_rows($result);
	for ($i = 0; $i < $noofrows; $i++) {
		$check = checkModuleActive('Products');
		if ($check == false) {
			break;
		}
		$output['productid'][$i] = $adb->query_result($result, $i, "productid");
		$output['productname'][$i] = decode_html($adb->query_result($result, $i, "productname"));
	}

	$userid = getPortalUserid();

	//We are going to display the picklist entries associated with admin user (role is H2)
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?", array($userid));
	$RowCount = $adb->num_rows($roleres);
	if ($RowCount > 0) {
		$admin_role = $adb->query_result($roleres, 0, 'roleid');
	}
	$result1 = $adb->pquery("select vtiger_ticketpriorities.ticketpriorities from vtiger_ticketpriorities inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_ticketpriorities.picklist_valueid and vtiger_role2picklist.roleid='$admin_role' order by sortorderid", []);
	for ($i = 0; $i < $adb->num_rows($result1); $i++) {
		$output['ticketpriorities'][$i] = array($adb->query_result($result1, $i, "ticketpriorities"), Vtiger_Language_Handler::getTranslatedString($adb->query_result($result1, $i, "ticketpriorities"), 'HelpDesk', vglobal('default_language')));
	}

	$result2 = $adb->pquery("select vtiger_ticketseverities.ticketseverities from vtiger_ticketseverities inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_ticketseverities.picklist_valueid and vtiger_role2picklist.roleid='$admin_role' order by sortorderid", []);
	for ($i = 0; $i < $adb->num_rows($result2); $i++) {
		$output['ticketseverities'][$i] = array($adb->query_result($result2, $i, "ticketseverities"), Vtiger_Language_Handler::getTranslatedString($adb->query_result($result2, $i, "ticketseverities"), 'HelpDesk', vglobal('default_language')));
	}

	$data = vtlib\Functions::getModuleFieldInfo(vtlib\Functions::getModuleId('HelpDesk'), 'ticketcategories');
	if ($data['presence'] != 1) {
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', [$data['fieldparams']]);
		$output['ticketcategories'] = [];
		while ($row = $adb->getRow($result)) {
			$output['ticketcategories'][] = [$row['name'], Vtiger_Language_Handler::getTranslatedString($row['name'], 'HelpDesk', vglobal('default_language'))];
		}
	}

	// Gather service contract information
	if (!\includes\Modules::isModuleActive('ServiceContracts')) {
		
	} else {
		$servicequery = "SELECT vtiger_servicecontracts.servicecontractsid,vtiger_servicecontracts.subject
							FROM vtiger_servicecontracts
							INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid=vtiger_servicecontracts.servicecontractsid
									AND vtiger_crmentity.deleted = 0
							WHERE vtiger_servicecontracts.sc_related_to = ?";
		$params = array($id);
		$showAll = show_all('HelpDesk');
		if ($showAll == 'true') {
			$servicequery .= ' || vtiger_servicecontracts.sc_related_to = (SELECT parentid FROM vtiger_contactdetails WHERE contactid=? && parentid <> 0)
								OR vtiger_servicecontracts.sc_related_to IN
											(SELECT contactid FROM vtiger_contactdetails WHERE parentid =
													(SELECT parentid FROM vtiger_contactdetails WHERE contactid=? && parentid <> 0))
							';
			array_push($params, $id);
			array_push($params, $id);
		}
		$serviceResult = $adb->pquery($servicequery, $params);

		for ($i = 0; $i < $adb->num_rows($serviceResult); $i++) {
			$serviceid = $adb->query_result($serviceResult, $i, 'servicecontractsid');
			$output['serviceid'][$i] = $serviceid;
			$output['servicename'][$i] = $adb->query_result($serviceResult, $i, 'subject');
		}
	}

	return array($output);
}

/** 	function to get the Knowledge base details
 * 	@param array $input_array - array which contains the following parameters
  =>	int $id - customer id
  string $sessionid - session id
 * 	return array $result - array which contains the faqcategory, all product ids , product names and all faq details
 */
function get_KBase_details($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$adb->println("Entering customer portal function get_KBase_details");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];

	if (!validateSession($id, $sessionid))
		return null;

	$userid = getPortalUserid();
	$result['faqcategory'] = [];
	$result['product'] = [];
	$result['faq'] = [];

	//We are going to display the picklist entries associated with admin user (role is H2)
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?", array($userid));
	$RowCount = $adb->num_rows($roleres);
	if ($RowCount > 0) {
		$admin_role = $adb->query_result($roleres, 0, 'roleid');
	}
	$category_query = "select vtiger_faqcategories.faqcategories from vtiger_faqcategories inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_faqcategories.picklist_valueid and vtiger_role2picklist.roleid='$admin_role'";
	$category_result = $adb->pquery($category_query, []);
	$category_noofrows = $adb->num_rows($category_result);
	for ($j = 0; $j < $category_noofrows; $j++) {
		$faqcategory = $adb->query_result($category_result, $j, 'faqcategories');
		$result['faqcategory'][$j] = array($faqcategory, Vtiger_Language_Handler::getTranslatedString($faqcategory, 'Faq', vglobal('default_language')));
	}

	$check = checkModuleActive('Products');

	if ($check == true) {
		$product_query = "select productid, productname from vtiger_products inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid where vtiger_crmentity.deleted=0";
		$product_result = $adb->pquery($product_query, []);
		$product_noofrows = $adb->num_rows($product_result);
		for ($i = 0; $i < $product_noofrows; $i++) {
			$productid = $adb->query_result($product_result, $i, 'productid');
			$productname = $adb->query_result($product_result, $i, 'productname');
			$result['product'][$i]['productid'] = $productid;
			$result['product'][$i]['productname'] = $productname;
		}
	}
	$faq_query = "select vtiger_faq.*, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime from vtiger_faq " .
		"inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_faq.id " .
		"where vtiger_crmentity.deleted=0 and vtiger_faq.status='Published' order by vtiger_crmentity.modifiedtime DESC";
	$faq_result = $adb->pquery($faq_query, []);
	$faq_noofrows = $adb->num_rows($faq_result);
	for ($k = 0; $k < $faq_noofrows; $k++) {
		$faqid = $adb->query_result($faq_result, $k, 'id');
		$moduleid = $adb->query_result($faq_result, $k, 'faq_no');
		$result['faq'][$k]['faqno'] = $moduleid;
		$result['faq'][$k]['id'] = $faqid;
		if ($check == true) {
			$result['faq'][$k]['product_id'] = $adb->query_result($faq_result, $k, 'product_id');
		}
		$result['faq'][$k]['question'] = nl2br($adb->query_result($faq_result, $k, 'question'));
		$result['faq'][$k]['answer'] = nl2br($adb->query_result($faq_result, $k, 'answer'));
		$result['faq'][$k]['category'] = $adb->query_result($faq_result, $k, 'category');
		$result['faq'][$k]['faqcreatedtime'] = $adb->query_result($faq_result, $k, 'createdtime');
		$result['faq'][$k]['faqmodifiedtime'] = $adb->query_result($faq_result, $k, 'modifiedtime');

		$faq_comment_query = "select * from vtiger_faqcomments where faqid=? order by createdtime DESC";
		$faq_comment_result = $adb->pquery($faq_comment_query, array($faqid));
		$faq_comment_noofrows = $adb->num_rows($faq_comment_result);
		for ($l = 0; $l < $faq_comment_noofrows; $l++) {
			$faqcomments = nl2br($adb->query_result($faq_comment_result, $l, 'comments'));
			$faqcreatedtime = $adb->query_result($faq_comment_result, $l, 'createdtime');
			if ($faqcomments != '') {
				$result['faq'][$k]['comments'][$l] = $faqcomments;
				$result['faq'][$k]['createdtime'][$l] = $faqcreatedtime;
			}
		}
	}
	$adb->println($result);
	return $result;
}

/** 	function to save the faq comment
 * 	@param array $input_array - array which contains the following values
  => 	int $id - Customer ie., Contact id
  int $sessionid - session id
  int $faqid - faq id
  string $comment - comment to be added with the FAQ
 * 	return array $result - This function will call get_KBase_details and return that array
 */
function save_faq_comment($input_array)
{
	$adb = PearDatabase::getInstance();
	$adb->println("Entering customer portal function save_faq_comment");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$faqid = (int) $input_array['faqid'];
	$comment = $input_array['comment'];

	if (!validateSession($id, $sessionid))
		return null;

	$createdtime = $adb->formatDate(date('YmdHis'), true);
	if (trim($comment) != '') {
		$faq_query = "insert into vtiger_faqcomments values(?,?,?,?)";
		$adb->pquery($faq_query, array('', $faqid, $comment, $createdtime));
	}

	$params = Array('id' => "$id", 'sessionid' => "$sessionid");
	$result = get_KBase_details($input_array);

	return $result;
}

/** function to get a list of tickets and to search tickets
 * @param array $input_array - array which contains the following values
  => 	int $id - Customer ie., Contact id
  int $only_mine - if true it will display only tickets related to contact
  otherwise displays tickets related to account it belongs and all the contacts that are under the same account
  int $where - used for searching tickets
  string $match - used for matching tickets
 * 	return array $result - This function will call get_KBase_details and return that array
 */
function get_tickets_list($input_array)
{

	//To avoid SQL injection we are type casting as well as bound the id variable.
	$id = (int) vtlib_purify($input_array['id']);

	$only_mine = $input_array['onlymine'];
	$where = vtlib_purifyForSql($input_array['where']); //addslashes is already added with where condition fields in portal itself
	$match = $input_array['match'];
	$sessionid = $input_array['sessionid'];

	if (!validateSession($id, $sessionid))
		return null;

	require_once('modules/HelpDesk/HelpDesk.php');
	require_once('include/utils/UserInfoUtil.php');

	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_ticket_list");

	$user = new Users();
	$userid = getPortalUserid();

	$show_all = show_all('HelpDesk');
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	// Prepare where conditions based on search query
	$join_type = '';
	$where_conditions = '';
	if (trim($where) != '') {
		if ($match == 'all' || $match == '') {
			$join_type = " && ";
		} elseif ($match == 'any') {
			$join_type = " || ";
		}
		$where = explode("&&&", $where);
		$where_conditions = implode($join_type, $where);
	}

	$entity_ids_list = [];
	if ($only_mine == 'true' || $show_all == 'false') {
		array_push($entity_ids_list, $id);
	} else {
		$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" && vtiger_crmentity.deleted = 0 " .
			" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?)  && parentid != 0) || contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id, $id));
		$no_of_cont = $adb->num_rows($contactres);
		for ($i = 0; $i < $no_of_cont; $i++) {
			$cont_id = $adb->query_result($contactres, $i, 'contactid');
			$acc_id = $adb->query_result($contactres, $i, 'parentid');
			if (!in_array($cont_id, $entity_ids_list))
				$entity_ids_list[] = $cont_id;
			if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
				$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new HelpDesk();
	$focus->filterInactiveFields('HelpDesk');
	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
		}
	}
	$query = sprintf("SELECT vtiger_troubletickets.*, vtiger_crmentity.smownerid,vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, '' AS setype
		FROM vtiger_troubletickets
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid && vtiger_crmentity.deleted = 0
		WHERE (vtiger_troubletickets.parent_id IN (%s) )", generateQuestionMarks($entity_ids_list));

	// Add conditions if there are any search parameters
	if ($join_type != '' && $where_conditions != '') {
		$query .= " && (" . $where_conditions . ")";
	}
	$params = array($entity_ids_list);


	$TicketsfieldVisibilityByColumn = [];
	foreach ($fields_list as $fieldlabel => $fieldname) {
		$TicketsfieldVisibilityByColumn[$fieldname] = getColumnVisibilityPermission($current_user->id, $fieldname, 'HelpDesk');
	}

	$res = $adb->pquery($query, $params);
	$noofdata = $adb->num_rows($res);
	for ($j = 0; $j < $noofdata; $j++) {
		$i = 0;
		foreach ($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $TicketsfieldVisibilityByColumn[$fieldname]; //in troubletickets the list_fields has columns so we call this API
			if ($fieldper == '1') {
				continue;
			}
			$output[0]['head'][0][$i]['fielddata'] = Vtiger_Language_Handler::getTranslatedString($fieldlabel, 'HelpDesk', vglobal('default_language'));
			$fieldvalue = $adb->query_result($res, $j, $fieldname);
			$ticketid = $adb->query_result($res, $j, 'ticketid');
			if ($fieldname == 'title') {
				$fieldvalue = '<a href="index.php?module=HelpDesk&action=index&fun=detail&id=' . $ticketid . '">' . $fieldvalue . '</a>';
			}
			if ($fieldname == 'parent_id') {
				$crmid = $fieldvalue;
				if ($crmid != '') {
					$fieldvalues = getEntityName('Accounts', array($crmid));
					$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
				} else {
					$fieldvalue = '';
				}
			}
			if ($fieldname == 'contact_id') {
				if (!empty($fieldvalue)) {
					$fieldvalues = getEntityName('Contacts', array($fieldvalue));
					$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $fieldvalue . '">' . $fieldvalues[$fieldvalue] . '</a>';
				} else {
					$fieldvalue = '';
				}
			}
			if ($fieldname == 'smownerid') {
				$fieldvalue = \includes\fields\Owner::getLabel($fieldvalue);
			}
			if (in_array($fieldname, array('priority', 'status'))) {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, 'HelpDesk', vglobal('default_language'));
			}
			$output[1]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customer portal function get_ticket_list");
	return $output;
}

/** 	function used to create ticket which has been created from customer portal
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  string $title - title of the ticket
  string $description - description of the ticket
  string $priority - priority of the ticket
  string $severity - severity of the ticket
  string $category - category of the ticket
  string $user_name - customer name
  int $parent_id - parent id ie., customer id as this customer is the parent for this ticket
  int $product_id - product id for the ticket
  string $module - module name where as based on this module we will get the module owner and assign this ticket to that corresponding user
 * 	return array - currently created ticket array, if this is not created then all tickets list will be returned
 */
function create_ticket($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$adb->println("Inside customer portal function create_ticket");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$title = $input_array['title'];
	$description = $input_array['description'];
	$priority = $input_array['priority'];
	$severity = $input_array['severity'];
	$category = $input_array['category'];
	$user_name = $input_array['user_name'];
	$parent_id = (int) $input_array['parent_id'];
	$product_id = (int) $input_array['product_id'];
	$module = $input_array['module'];
	$servicecontractid = (int) $input_array['serviceid'];
	$projectid = (int) $input_array['projectid'];

	if (!validateSession($id, $sessionid))
		return null;

	$ticket = CRMEntity::getInstance('HelpDesk');

	$ticket->column_fields[ticket_title] = vtlib_purify($title);
	$ticket->column_fields[description] = vtlib_purify($description);
	$ticket->column_fields[ticketpriorities] = $priority;
	$ticket->column_fields[ticketseverities] = $severity;
	$ticket->column_fields[ticketcategories] = $category;
	$ticket->column_fields[ticketstatus] = 'Open';
	$ticket->column_fields[product_id] = $product_id;
	if ($servicecontractid != 0)
		$ticket->column_fields[servicecontractsid] = $servicecontractid;
	if ($projectid != 0)
		$ticket->column_fields[projectid] = $projectid;
	$defaultAssignee = getDefaultAssigneeId();

	$ticket->column_fields['assigned_user_id'] = $defaultAssignee;
	$ticket->column_fields['from_portal'] = 1;

	$accountResult = $adb->pquery('SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?', array($parent_id));
	$accountId = $adb->query_result($accountResult, 0, 'parentid');
	if (!empty($accountId))
		$ticket->column_fields['parent_id'] = $accountId;

	$ticket->save('HelpDesk');
	relateEntities($ticket, 'HelpDesk', $ticket->id, 'Contacts', $parent_id);

	$ticketresult = $adb->pquery("select vtiger_troubletickets.ticketid from vtiger_troubletickets
		inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
		where vtiger_crmentity.deleted=0 and vtiger_troubletickets.ticketid = ?", array($ticket->id));
	if ($adb->num_rows($ticketresult) == 1) {
		$record_save = 1;
		$record_array[0]['new_ticket']['ticketid'] = $adb->query_result($ticketresult, 0, 'ticketid');
	}
	if ($record_save == 1) {
		$adb->println("Ticket from Portal is saved with id => " . $ticket->id);
		return $record_array;
	} else {
		$adb->println("There may be error in saving the ticket.");
		return null;
	}
}

/** 	function used to update the ticket comment which is added from the customer portal
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  int $ticketid - ticket id
  int $ownerid - customer ie., contact id who has added this ticket comment
  string $comments - comment which is added from the customer portal
 * 	return void
 */
function update_ticket_comment($input_array)
{
	global $mod_strings;
	$adb = PearDatabase::getInstance();
	$adb->println("Inside customer portal function update_ticket_comment");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];
	$ownerid = (int) $input_array['ownerid'];
	$comments = $input_array['comments'];

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	if (!validateSession($id, $sessionid))
		return null;

	if (trim($comments) != '') {
		$modComments = CRMEntity::getInstance('ModComments');
		$modComments->column_fields['commentcontent'] = vtlib_purify($comments);
		$modComments->column_fields['assigned_user_id'] = $current_user->id;
		$modComments->column_fields['customer'] = $ownerid;
		$modComments->column_fields['related_to'] = $ticketid;
		$modComments->column_fields['from_portal'] = true;
		$modComments->save('ModComments');
	}
}

/** 	function used to close the ticket
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  int $ticketid - ticket id
 * 	return string - success or failure message will be returned based on the ticket close update query
 */
function close_current_ticket($input_array)
{
	global $mod_strings;
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();

	$current_user = vglobal('current_user');
	require_once('modules/HelpDesk/HelpDesk.php');
	$adb->println("Inside customer portal function close_current_ticket");
	$adb->println($input_array);

	//foreach($input_array as $fieldname => $fieldvalue)$input_array[$fieldname] = mysql_real_escape_string($fieldvalue);
	$userid = getPortalUserid();

	$current_user->id = $userid;
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if (!validateSession($id, $sessionid))
		return null;

	$focus = new HelpDesk();
	$focus->id = $ticketid;
	$focus->retrieve_entity_info($focus->id, 'HelpDesk');
	$focus->mode = 'edit';
	$focus->column_fields = array_map(decode_html, $focus->column_fields);
	$focus->column_fields['ticketstatus'] = 'Closed';
	// Blank out the comments information to avoid un-necessary duplication
	$focus->column_fields['comments'] = '';
	$focus->column_fields['from_portal'] = 1;
	// END
	$focus->save("HelpDesk");
	return "closed";
}

/** 	function used to authenticate whether the customer has access or not
 * 	@param string $username - customer name for the customer portal
 * 	@param string $password - password for the customer portal
 * 	@param string $login - true or false. If true means function has been called for login process and we have to clear the session if any, false means not called during login and we should not unset the previous sessions
 * 	return array $list - returns array with all the customer details
 */
function authenticate_user($username, $password, $version, $portalLang, $login = 'true')
{
	$adb = vglobal('adb');
	$log = LoggerManager::getInstance();
	$currentLanguage = vglobal('current_language');

	$adb->println("Inside customer portal function authenticate_user($username, $password, $login).");
	include('config/version.php');
	if (version_compare($version, $YetiForce_current_version, '>=') == 0) {
		$list[0] = "NOT COMPATIBLE";
		return $list;
	}

	$password = CustomerPortalPassword::encryptPassword($password, $username);
	$currentDate = date("Y-m-d");

	$sql = "select id, user_name, user_password,last_login_time, support_start_date, support_end_date
				from vtiger_portalinfo
					inner join vtiger_customerdetails on vtiger_portalinfo.id=vtiger_customerdetails.customerid
					inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_portalinfo.id
				where vtiger_crmentity.deleted=0 and user_name=? and user_password = ?
					and isactive=1 and vtiger_customerdetails.portal=1
					and vtiger_customerdetails.support_start_date <= ? and vtiger_customerdetails.support_end_date >= ?";
	$result = $adb->pquery($sql, array($username, $password, $currentDate, $currentDate));

	$num_rows = $adb->num_rows($result);
	if ($num_rows > 1)
		return array('err1' => "LBL_MORE_THAN_ONE_USER"); //More than one user
	elseif ($num_rows <= 0)
		return array('err1' => "LBL_INVALID_USERNAME_OR_PASSWORD"); //No user

	$customerid = $adb->query_result($result, 0, 'id');
	$list[0]['id'] = $customerid;
	$list[0]['user_name'] = $adb->query_result($result, 0, 'user_name');
	$list[0]['user_password'] = $adb->query_result($result, 0, 'user_password');
	$list[0]['last_login_time'] = $adb->query_result($result, 0, 'last_login_time');
	$list[0]['support_start_date'] = $adb->query_result($result, 0, 'support_start_date');
	$list[0]['support_end_date'] = $adb->query_result($result, 0, 'support_end_date');
	$currentLanguage = $portalLang;
	vglobal('default_language', $currentLanguage);

	//During login process we will pass the value true. Other times (change password) we will pass false
	if ($login != 'false') {
		$sessionid = \vtlib\Functions::generateRandomPassword();
		unsetServerSessionId($customerid);
		$sql = "insert into vtiger_soapservice values(?,?,?,?)";
		$result = $adb->pquery($sql, array($customerid, 'customer', $sessionid, $portalLang));
		$list[0]['sessionid'] = $sessionid;
	}
	update_login_details($customerid, $sessionid, 'login');
	return $list;
}

/** 	function used to change the password for the customer portal
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  string $username - customer name
  string $password - new password to change
 * 	return array $list - returns array with all the customer details
 */
function change_password($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function change_password");
	$adb->println($input_array);

	$id = (int) $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$userName = $input_array['username'];
	$old_password = $input_array['old_password'];
	$newPassword = $input_array['new_password'];
	$version = $input_array['version'];

	if (!validateSession($id, $sessionid))
		return null;

	$list = authenticate_user($userName, $old_password, $version, 'false');
	if (!empty($list[0]['id'])) {
		$newPassword = CustomerPortalPassword::encryptPassword($newPassword, $userName);

		$sql = "update vtiger_portalinfo set user_password=? where id=? and user_name=?";
		$result = $adb->pquery($sql, array($newPassword, $id, $userName));
		$list = array('LBL_PASSWORD_CHANGED');
	}
	$log->debug("Exiting customer portal function change_password");
	return $list;
}

/** 	function used to update the login details for the customer
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  string $flag - login/logout, based on this flag, login or logout time will be updated for the customer
 * 	return string $list - empty value
 */
function update_login_details($id, $sessionid, $flag)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function update_login_details");
	$adb->println("INPUT ARRAY for the function update_login_details");

	$current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);

	if ($flag == 'login') {
		$sql = "update vtiger_portalinfo set login_time=? where id=?";
		$result = $adb->pquery($sql, array($current_time, $id));
	} elseif ($flag == 'logout') {
		$sql = "update vtiger_portalinfo set logout_time=?, last_login_time=login_time where id=?";
		$result = $adb->pquery($sql, array($current_time, $id));
	}
	$log->debug("Exiting customer portal function update_login_details");
}

/** function used to send mail to the customer when he forgot the password and want to retrieve the password
 * 	@param string $mailid - email address of the customer
 * 	return message about the mail sending whether entered mail id is correct or not or is there any problem in mail sending
 */
function send_mail_for_password($mailid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	vimport('modules.Settings.CustomerPortal.helpers.CustomerPortalPassword');
	$log->debug('Entering customer portal function send_mail_for_password');
	$adb->println("Inside the function send_mail_for_password($mailid).");

	$sql = 'select * from vtiger_portalinfo where user_name = ?;';
	$res = $adb->pquery($sql, array($mailid));
	if ($adb->num_rows($res) > 0) {
		$user_name = $adb->query_result($res, 0, 'user_name');
		$isactive = $adb->query_result($res, 0, 'isactive');
		$record = $adb->query_result($res, 0, 'id');

		// generate new temp password for portal user
		$password = \vtlib\Functions::generateRandomPassword();
		$truePassword = $password;
		$password = CustomerPortalPassword::encryptPassword($password, $user_name);
		$params = array($password, CustomerPortalPassword::getCryptType(), $truePassword, $record);
		$sql = 'UPDATE vtiger_portalinfo SET `user_password` = ?, `crypt_type` = ?, `password_sent` = ? WHERE `id` = ? LIMIT 1;';
		$adb->pquery($sql, $params);

		$data = [
			'sysname' => 'YetiPortalForgotPassword',
			'to_email' => $mailid,
			'module' => 'Contacts',
			'record' => $record,
			'user_name' => $user_name,
			'password' => $truePassword,
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
	}
	$succes = false;
	if ($mailid == '') {
		$masage = 'LBL_NO_MAIL_ADDRESS';
	} elseif ($adb->num_rows($res) == 0) {
		$masage = 'LBL_EMAIL_ADDRESS_NOT_FOUND';
	} elseif ($isactive == 0) {
		$masage = 'LBL_LOGIN_REVOKED';
	} elseif (!$recordModel->sendMailFromTemplate($data)) {
		$masage = 'LBL_MAIL_COULDNOT_SENT';
	} else {
		$succes = true;
		$masage = 'LBL_PASSWORD_HAS_BEEN_SENT';
		$params = [1, $record];
		$sql = 'UPDATE vtiger_portalinfo SET `password_sent` = ? WHERE `id` = ? LIMIT 1;';
		$adb->pquery($sql, $params);
	}
	$ret_msg = array('succes' => $succes, 'masage' => $masage);
	$adb->println("Exit from send_mail_for_password. $ret_msg");
	$log->debug('Exiting customer portal function send_mail_for_password');
	return $ret_msg;
}

/** 	function used to get the ticket creater
 * 	@param array $input_array - array which contains the following values
  =>	int $id - customer ie., contact id
  int $sessionid - session id
  int $ticketid - ticket id
 * 	return int $creator - ticket created user id will be returned ie., smcreatorid from crmentity table
 */
function get_ticket_creator($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_ticket_creator");
	$adb->println("INPUT ARRAY for the function get_ticket_creator");
	$adb->println($input_array);

	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = (int) $input_array['ticketid'];

	if (!validateSession($id, $sessionid))
		return null;

	$res = $adb->pquery("select smcreatorid from vtiger_crmentity where crmid=?", array($ticketid));
	$creator = $adb->query_result($res, 0, 'smcreatorid');
	$log->debug("Exiting customer portal function get_ticket_creator");
	return $creator;
}

/** 	function used to get the picklist values
 * 	@param array $input_array - array which contains the following values
  =>	int $id - customer ie., contact id
  int $sessionid - session id
  string $picklist_name - picklist name you want to retrieve from database
 * 	return array $picklist_array - all values of the corresponding picklist will be returned as a array
 */
function get_picklists($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_picklists");
	$adb->println("INPUT ARRAY for the function get_picklists");
	$adb->println($input_array);

	//To avoid SQL injection we are type casting as well as bound the id variable
	$id = (int) vtlib_purify($input_array['id']);
	$sessionid = $input_array['sessionid'];
	//To avoid SQL injection.
	$picklist_name = vtlib_purifyForSql($input_array['picklist_name']);
	if (empty($picklist_name))
		return null;

	if (!validateSession($id, $sessionid))
		return null;

	$picklist_array = [];

	$admin_role = 'H2';
	$userid = getPortalUserid();
	$roleres = $adb->pquery("SELECT roleid from vtiger_user2role where userid = ?", array($userid));
	$RowCount = $adb->num_rows($roleres);
	if ($RowCount > 0) {
		$admin_role = $adb->query_result($roleres, 0, 'roleid');
	}

	$res = $adb->pquery(sprintf('SELECT vtiger_%s.* FROM vtiger_%s
		INNER JOIN vtiger_role2picklist ON vtiger_role2picklist.picklistvalueid = vtiger_%s.picklist_valueid 
		AND vtiger_role2picklist.roleid = ?', $picklist_name, $picklist_name, $picklist_name), [$admin_role]);
	for ($i = 0; $i < $adb->num_rows($res); $i++) {
		$picklist_val = $adb->query_result($res, $i, $picklist_name);
		$picklist_array[$i] = $picklist_val;
	}

	$adb->println($picklist_array);
	$log->debug("Exiting customer portal function get_picklists($picklist_name)");
	return $picklist_array;
}

/** 	function to get the attachments of a ticket
 * 	@param array $input_array - array which contains the following values
  =>	int $id - customer ie., contact id
  int $sessionid - session id
  int $ticketid - ticket id
 * 	return array $output - This will return all the file details related to the ticket
 */
function get_ticket_attachments($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_ticket_attachments");
	$adb->println("INPUT ARRAY for the function get_ticket_attachments");
	$adb->println($input_array);

	$check = checkModuleActive('Documents');
	if ($check == false) {
		return array(array('error' => "#MODULE NO ACTIVE#"));
	}
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = $input_array['ticketid'];

	$isPermitted = check_permission($id, 'HelpDesk', $ticketid);
	if ($isPermitted == false) {
		return array(array('error' => "#NOT AUTHORIZED#"));
	}

	if (!validateSession($id, $sessionid))
		return array(array('error' => "#INVALIDATE SESSION#"));

	$query = 'select vtiger_troubletickets.ticketid, vtiger_attachments.*,vtiger_notes.filename,vtiger_notes.filelocationtype from vtiger_troubletickets 
	left join vtiger_senotesrel on vtiger_senotesrel.crmid=vtiger_troubletickets.ticketid 
	left join vtiger_notes on vtiger_notes.notesid=vtiger_senotesrel.notesid 
	inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_notes.notesid 
	left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid=vtiger_notes.notesid 
	left join vtiger_attachments on vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid 
	WHERE vtiger_troubletickets.ticketid = ? && vtiger_crmentity.deleted = 0;';
	$res = $adb->pquery($query, array($ticketid));
	$noofrows = $adb->num_rows($res);
	$output = [];
	for ($i = 0; $i < $noofrows; $i++) {
		$filename = $adb->query_result_raw($res, $i, 'filename');
		$filepath = $adb->query_result_raw($res, $i, 'path');
		$fileid = $adb->query_result_raw($res, $i, 'attachmentsid');
		$filesize = filesize($filepath . $fileid . "_" . $filename);
		$filetype = $adb->query_result_raw($res, $i, 'type');
		$filelocationtype = $adb->query_result_raw($res, $i, 'filelocationtype');

		$output[$i]['fileid'] = $fileid;
		$output[$i]['filename'] = $filename;
		$output[$i]['filetype'] = $filetype;
		$output[$i]['filesize'] = $filesize;
		$output[$i]['filelocationtype'] = $filelocationtype;
	}
	$log->debug("Exiting customer portal function get_ticket_attachments");
	return array($output);
}

/** 	function used to get the contents of a file
 * 	@param array $input_array - array which contains the following values
  =>	int $id - customer ie., contact id
  int $sessionid - session id
  int $fileid - id of the file to which we want contents
  string $filename - name of the file to which we want contents
 * 	return $filecontents array with single file contents like [fileid] => filecontent
 */
function get_filecontent($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_filecontent");
	$adb->println("INPUT ARRAY for the function get_filecontent");
	$adb->println($input_array);
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$fileid = $input_array['fileid'];
	$filename = $input_array['filename'];
	$ticketid = $input_array['ticketid'];
	if (!validateSession($id, $sessionid))
		return null;

	$query = 'SELECT vtiger_attachments.path FROM vtiger_attachments
	INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
	INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_seattachmentsrel.crmid
	INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid = vtiger_notes.notesid
	INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_senotesrel.crmid
	WHERE vtiger_troubletickets.ticketid = ? && vtiger_attachments.name = ? && vtiger_attachments.attachmentsid = ?';
	$res = $adb->pquery($query, array($ticketid, $filename, $fileid));
	if ($adb->num_rows($res) > 0) {
		$filenamewithpath = $adb->query_result($res, 0, 'path') . $fileid . "_" . $filename;
		$filecontents[$fileid] = base64_encode(file_get_contents($filenamewithpath));
	}
	$log->debug("Exiting customer portal function get_filecontent ");
	return $filecontents;
}

/** 	function to add attachment for a ticket ie., the passed contents will be write in a file and the details will be stored in database
 * 	@param array $input_array - array which contains the following values
  =>	int $id - customer ie., contact id
  int $sessionid - session id
  int $ticketid - ticket id
  string $filename - file name to be attached with the ticket
  string $filetype - file type
  int $filesize - file size
  string $filecontents - file contents as base64 encoded format
 * 	return void
 */
function add_ticket_attachment($input_array)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function add_ticket_attachment");
	$adb->println("INPUT ARRAY for the function add_ticket_attachment");
	$adb->println($input_array);
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$ticketid = $input_array['ticketid'];
	$filename = $input_array['filename'];
	$filetype = $input_array['filetype'];
	$filesize = $input_array['filesize'];
	$filecontents = $input_array['filecontents'];

	if (!validateSession($id, $sessionid))
		return null;

	//decide the file path where we should upload the file in the server
	$upload_filepath = \vtlib\Functions::initStorageFileDirectory();

	$attachmentid = $adb->getUniqueID("vtiger_crmentity");

	//fix for space in file name
	$filename = \includes\fields\File::sanitizeUploadFileName($filename);
	$new_filename = $attachmentid . '_' . $filename;

	$data = base64_decode($filecontents);
	$description = 'CustomerPortal Attachment';

	//write a file with the passed content
	$handle = @fopen($upload_filepath . $new_filename, 'w');
	fputs($handle, $data);
	fclose($handle);

	//Now store this file information in db and relate with the ticket
	$date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);

	$crmquery = "insert into vtiger_crmentity (crmid,setype,description,createdtime) values(?,?,?,?)";
	$crmresult = $adb->pquery($crmquery, array($attachmentid, 'HelpDesk Attachment', $description, $date_var));

	$attachmentquery = "insert into vtiger_attachments(attachmentsid,name,description,type,path) values(?,?,?,?,?)";
	$attachmentreulst = $adb->pquery($attachmentquery, array($attachmentid, $filename, $description, $filetype, $upload_filepath));

	$relatedquery = "insert into vtiger_seattachmentsrel values(?,?)";
	$relatedresult = $adb->pquery($relatedquery, array($ticketid, $attachmentid));

	$user_id = getDefaultAssigneeId();

	require_once('modules/Documents/Documents.php');
	$focus = new Documents();
	$focus->column_fields['notes_title'] = $filename;
	$focus->column_fields['filename'] = $filename;
	$focus->column_fields['filetype'] = $filetype;
	$focus->column_fields['filesize'] = $filesize;
	$focus->column_fields['filelocationtype'] = 'I';
	$focus->column_fields['filedownloadcount'] = 0;
	$focus->column_fields['filestatus'] = 1;
	$focus->column_fields['assigned_user_id'] = $user_id;
	$focus->column_fields['folderid'] = 1;
	$focus->parent_id = $ticketid;
	$focus->save('Documents');

	$related_doc = 'insert into vtiger_seattachmentsrel values (?,?)';
	$res = $adb->pquery($related_doc, array($focus->id, $attachmentid));

	$tic_doc = 'insert into vtiger_senotesrel values(?,?)';
	$res = $adb->pquery($tic_doc, array($ticketid, $focus->id));
	$log->debug("Exiting customer portal function add_ticket_attachment");
}

/** 	Function used to validate the session
 * 	@param int $id - contact id to which we want the session id
 * 	@param string $sessionid - session id which will be passed from customerportal
 * 	return true/false - return true if valid session otherwise return false
 * */
function validateSession($id, $sessionid)
{
	$adb = PearDatabase::getInstance();
	$adb->println("Inside function validateSession($id, $sessionid)");

	if (empty($sessionid))
		return false;

	$server_sessionid = getServerSessionId($id);

	$adb->println("Checking Server session id and customer input session id ==> $server_sessionid == $sessionid");

	if ($server_sessionid == $sessionid) {
		$adb->println("Session id match. Authenticated to do the current operation.");
		return true;
	} else {
		$adb->println("Session id does not match. Not authenticated to do the current operation.");
		return false;
	}
}

/** 	Function used to get the session id which was set during login time
 * 	@param int $id - contact id to which we want the session id
 * 	return string $sessionid - return the session id for the customer which is a random alphanumeric character string
 * */
function getServerSessionId($id)
{
	$adb = PearDatabase::getInstance();
	$adb->println("Inside the function getServerSessionId($id)");

	//To avoid SQL injection we are type casting as well as bound the id variable. In each and every function we will call this function
	$id = (int) $id;

	$sessionid = Vtiger_Soap_YetiPortal::lookupSessionId($id);
	if ($sessionid === false) {
		$query = "select * from vtiger_soapservice where type='customer' and id=?";
		$result = $adb->pquery($query, array($id));
		if ($adb->num_rows($result) > 0) {
			$sessionid = $adb->query_result($result, 0, 'sessionid');
			$current_language = $adb->query_result($result, 0, 'lang');
			vglobal('default_language', $current_language);
			Vtiger_Soap_YetiPortal::updateSessionId($id, $sessionid);
		}
	}
	return $sessionid;
}

/** 	Function used to unset the server session id for the customer
 * 	@param int $id - contact id to which customer we want to unset the session id
 * */
function unsetServerSessionId($id)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function unsetServerSessionId");
	$adb->println("Inside the function unsetServerSessionId");

	$id = (int) $id;
	Vtiger_Soap_YetiPortal::updateSessionId($id, false);

	$adb->pquery("delete from vtiger_soapservice where type='customer' and id=?", array($id));
	$log->debug("Exiting customer portal function unsetServerSessionId");
	return;
}

function getServerSession($id)
{
	$adb = PearDatabase::getInstance();
	$id = (int) $id;
	$session = Vtiger_Soap_YetiPortal::$_session[$id];
	if (!$session) {
		$query = "select * from vtiger_soapservice where type='customer' and id=?";
		$result = $adb->pquery($query, array($id));
		if ($adb->num_rows($result) > 0) {
			Vtiger_Soap_YetiPortal::$_session[$id] = $adb->query_result_rowdata($result, 0);
			if (!Vtiger_Soap_YetiPortal::$_session[$id]['lang'])
				Vtiger_Soap_YetiPortal::$_session[$id]['lang'] = vglobal('default_language');
			$session = Vtiger_Soap_YetiPortal::$_session[$id];
		}
	}
	return $session;
}

/** 	function used to get the Account name
 * 	@param int $id - Account id
 * 	return string $message - Account name returned
 */
function get_account_name($accountid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_account_name");
	$res = $adb->pquery("select accountname from vtiger_account where accountid=?", array($accountid));
	$accountname = $adb->query_result($res, 0, 'accountname');
	$log->debug("Exiting customer portal function get_account_name");
	return $accountname;
}

/** function used to get the Contact name
 *  @param int $id -Contact id
 * return string $message -Contact name returned
 */
function get_contact_name($contactid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_contact_name");
	$contact_name = '';
	if ($contactid != '') {
		$sql = "select firstname,lastname from vtiger_contactdetails where contactid=?";
		$result = $adb->pquery($sql, array($contactid));
		$firstname = $adb->query_result($result, 0, "firstname");
		$lastname = $adb->query_result($result, 0, "lastname");
		$contact_name = $firstname . " " . $lastname;
		return $contact_name;
	}
	$log->debug("Exiting customer portal function get_contact_name");
	return false;
}

/**     function used to get the Account id
 * *      @param int $id - Contact id
 * *      return string $message - Account id returned
 * */
function get_check_account_id($id)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_check_account_id");
	$res = $adb->pquery("select parentid from vtiger_contactdetails where contactid=?", array($id));
	$accountid = $adb->query_result($res, 0, 'parentid');
	$log->debug("Entering customer portal function get_check_account_id");
	return $accountid;
}

/** 	function used to get the vendor name
 * 	@param int $id - vendor id
 * 	return string $name - Vendor name returned
 */
function get_vendor_name($vendorid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_vendor_name");
	$res = $adb->pquery("select vendorname from vtiger_vendor where vendorid=?", array($vendorid));
	$name = $adb->query_result($res, 0, 'vendorname');
	$log->debug("Exiting customer portal function get_vendor_name");
	return $name;
}

/** 	function used to get the Invoice List
 * 	@param int $id - id -Contactid
 * 	return string $output - Invoice list Array
 */
function get_list_values($id, $module, $sessionid, $only_mine = 'true')
{
	\vtlib\Deprecated::checkFileAccessForInclusion('modules/' . $module . '/' . $module . '.php');
	require_once('modules/' . $module . '/' . $module . '.php');
	require_once('include/utils/UserInfoUtil.php');
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_list_values");
	$check = checkModuleActive($module);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}

	//To avoid SQL injection we are type casting as well as bound the id variable.
	$id = (int) vtlib_purify($id);
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	$focus = new $module();
	$focus->filterInactiveFields($module);
	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
		}
	}

	if (!validateSession($id, $sessionid))
		return null;

	$entity_ids_list = [];
	$show_all = show_all($module);
	if ($only_mine == 'true' || $show_all == 'false') {
		array_push($entity_ids_list, $id);
	} else {
		$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" && vtiger_crmentity.deleted = 0 " .
			" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?)  && parentid != 0) || contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id, $id));
		$no_of_cont = $adb->num_rows($contactres);
		for ($i = 0; $i < $no_of_cont; $i++) {
			$cont_id = $adb->query_result($contactres, $i, 'contactid');
			$acc_id = $adb->query_result($contactres, $i, 'parentid');
			if (!in_array($cont_id, $entity_ids_list))
				$entity_ids_list[] = $cont_id;
			if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
				$entity_ids_list[] = $acc_id;
		}
	}

	if ($module == 'Documents') {
		$result = $adb->pquery('SELECT fieldparams FROM vtiger_field WHERE columnname = ? && tablename = ?', ['folderid', 'vtiger_notes']);
		$tree = $adb->query_result($result, 0, 'fieldparams');

		$query = "select vtiger_notes.*, vtiger_crmentity.*, vtiger_senotesrel.crmid as entityid, '' as setype, vtiger_trees_templates_data.label as foldername from vtiger_notes " .
			"inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_notes.notesid " .
			"left join vtiger_senotesrel on vtiger_senotesrel.notesid=vtiger_notes.notesid " .
			"LEFT JOIN vtiger_trees_templates_data ON vtiger_trees_templates_data.tree = vtiger_notes.folderid " .
			"where vtiger_crmentity.deleted = 0 and vtiger_trees_templates_data.templateid = ? and vtiger_senotesrel.crmid in (" . generateQuestionMarks($entity_ids_list) . ")";
		$params = array($tree, $entity_ids_list);
		$fields_list['Related To'] = 'entityid';
	} else if ($module == 'Contacts') {
		$query = sprintf("select vtiger_contactdetails.*,vtiger_crmentity.smownerid from vtiger_contactdetails
		 inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
		 where vtiger_crmentity.deleted = 0 and contactid IN (%s)", generateQuestionMarks($entity_ids_list));
		$params = array($entity_ids_list);
	} else if ($module == 'Assets') {
		$accountRes = $adb->pquery("SELECT parentid FROM vtiger_contactdetails
						INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						WHERE contactid = ? && deleted = 0", array($id));
		$accountRow = $adb->num_rows($accountRes);
		if ($accountRow) {
			$accountid = $adb->query_result($accountRes, 0, 'parentid');
			$query = "select vtiger_assets.*, vtiger_assets.parent_id as entityid , vtiger_crmentity.smownerid from vtiger_assets
							inner join vtiger_crmentity on vtiger_assets.assetsid = vtiger_crmentity.crmid
							left join vtiger_account on vtiger_account.accountid = vtiger_assets.parent_id
							left join vtiger_products on vtiger_products.productid = vtiger_assets.product
							where vtiger_crmentity.deleted = 0 and parent_id = ?";
			$params = array($accountid);
			$fields_list['Related To'] = 'entityid';
		}
	} else if ($module == 'Project') {
		$query = sprintf("SELECT vtiger_project.*, vtiger_crmentity.smownerid
					FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
					WHERE vtiger_crmentity.deleted = 0 && vtiger_project.linktoaccountscontacts IN (%s)", generateQuestionMarks($entity_ids_list));
		$params = array($entity_ids_list);
		$fields_list['Related To'] = 'linktoaccountscontacts';
	}

	$res = $adb->pquery($query, $params);
	$noofdata = $adb->num_rows($res);

	$columnVisibilityByFieldnameInfo = [];
	if ($noofdata) {
		foreach ($fields_list as $fieldlabel => $fieldname) {
			$columnVisibilityByFieldnameInfo[$fieldname] = getColumnVisibilityPermission($current_user->id, $fieldname, $module);
		}
	}

	for ($j = 0; $j < $noofdata; $j++) {
		$i = 0;
		foreach ($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $columnVisibilityByFieldnameInfo[$fieldname];
			if ($fieldper == '1' && $fieldname != 'entityid') {
				continue;
			}
			$fieldlabelOrg = $fieldlabel;
			$fieldlabel = Vtiger_Language_Handler::getTranslatedString($fieldlabel, $module, vglobal('default_language'));

			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel;
			$fieldvalue = $adb->query_result($res, $j, $fieldname);
			$fieldValuesToRound = array('total', 'subtotal', 'discount_amount', 'pre_tax_total', 'received', 'balance', 'unit_price');

			if ($module == 'Documents') {
				if ($fieldname == 'title') {
					$fieldid = $adb->query_result($res, $j, 'notesid');
					$fieldvalue = '<a href="index.php?&module=Documents&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				}
				if ($fieldname == 'filename') {
					$fieldid = $adb->query_result($res, $j, 'notesid');
					$filename = $fieldvalue;
					$folderid = $adb->query_result($res, $j, 'folderid');
					$filename = $adb->query_result($res, $j, 'filename');
					$fileactive = $adb->query_result($res, $j, 'filestatus');
					$filetype = $adb->query_result($res, $j, 'filelocationtype');

					if ($fileactive == 1) {
						if ($filetype == 'I') {
							$fieldvalue = '<a href="index.php?&downloadfile=true&folderid=' . $folderid . '&filename=' . $filename . '&module=Documents&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
						} elseif ($filetype == 'E') {
							$fieldvalue = '<a target="_blank" href="' . $filename . '" onclick = "updateCount(' . $fieldid . ');">' . $filename . '</a>';
						}
					} else {
						$fieldvalue = $filename;
					}
				}
				if ($fieldname == 'folderid') {
					$fieldvalue = Vtiger_Language_Handler::getTranslatedString($adb->query_result($res, $j, 'foldername'), $module, vglobal('default_language'));
				}
			}
			if ($module == 'Services') {
				if ($fieldname == 'servicename') {
					$fieldid = $adb->query_result($res, $j, 'serviceid');
					$fieldvalue = '<a href="index.php?module=Services&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				}
				if ($fieldname == 'discontinued') {
					if ($fieldvalue == 1) {
						$fieldvalue = 'Yes';
					} else {
						$fieldvalue = 'No';
					}
				}
				if (in_array($fieldname, $fieldValuesToRound)) {
					$fieldvalue = round($fieldvalue, 2);
				}
				if ($fieldname == 'unit_price') {
					$sym = getCurrencySymbol($res, $j, 'currency_id');
					$fieldvalue = $sym . $fieldvalue;
				}
			}
			if ($module == 'Contacts') {
				if ($fieldname == 'lastname' || $fieldname == 'firstname') {
					$fieldid = $adb->query_result($res, $j, 'contactid');
					$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				} elseif ($fieldname == 'parentid') {
					$fieldvalue = vtlib\Functions::getCRMRecordLabel($fieldvalue);
				}
			}
			if ($module == 'Project') {
				if ($fieldname == 'projectname') {
					$fieldid = $adb->query_result($res, $j, 'projectid');
					$fieldvalue = '<a href="index.php?module=Project&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				}
				if ($fieldname == 'projecttype') {
					$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, $module, vglobal('default_language'));
				}
			}
			if ($module == 'ProjectTask') {
				if ($fieldname == 'projecttaskname') {
					$fieldid = $adb->query_result($res, $j, 'projecttaskid');
					$fieldvalue = '<a href="index.php?module=ProjectTask&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				}
			}
			if ($module == 'ProjectMilestone') {
				if ($fieldname == 'projectmilestonename') {
					$fieldid = $adb->query_result($res, $j, 'projectmilestoneid');
					$fieldvalue = '<a href="index.php?module=ProjectMilestone&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				}
			}
			if ($fieldname == 'entityid' || $fieldname == 'contactid' || $fieldname == 'accountid' || $fieldname == 'account' || $fieldname == 'linktoaccountscontacts') {
				$crmid = $fieldvalue;
				$modulename = \vtlib\Functions::getCRMRecordType($crmid);
				if ($crmid != '' && $modulename != '') {
					$fieldvalues = getEntityName($modulename, array($crmid));
					if ($modulename == 'Contacts')
						$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
					elseif ($modulename == 'Accounts')
						$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
				} else {
					$fieldvalue = '';
				}
			}
			if ($module == 'Assets' && $fieldname == 'assetname') {
				$assetname = $fieldvalue;
				$assetid = $adb->query_result($res, $j, 'assetsid');
				$fieldvalue = '<a href="index.php?module=Assets&action=index&id=' . $assetid . '">' . $assetname . '</a>';
			}
			if ($fieldname == 'product' && $module == 'Assets') {
				$crmid = $adb->query_result($res, $j, 'product');
				$fres = $adb->pquery('select vtiger_products.productname from vtiger_products where productid=?', array($crmid));
				$productname = $adb->query_result($fres, 0, 'productname');
				$fieldvalue = '<a href="index.php?module=Products&action=index&id=' . $crmid . '">' . $productname . '</a>';
			}
			if ($fieldname == 'smownerid') {
				$fieldvalue = \includes\fields\Owner::getLabel($fieldvalue);
			}
			if (in_array($fieldlabelOrg, array('Status'))) {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, $module, vglobal('default_language'));
			}

			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customer portal function get_list_values");
	return $output;
}

/** 	function used to get the contents of a file
 * 	@param int $id - customer ie., id
 * 	return $filecontents array with single file contents like [fileid] => filecontent
 */
function get_filecontent_detail($id, $folderid, $module, $customerid, $sessionid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$site_URL = AppConfig::main('site_URL');
	$log->debug("Entering customer portal function get_filecontent_detail ");
	$isPermitted = check_permission($customerid, $module, $id);
	if ($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if (!validateSession($customerid, $sessionid))
		return null;

	if ($module == 'Documents') {
		$query = "SELECT filetype FROM vtiger_notes WHERE notesid =?";
		$res = $adb->pquery($query, array($id));
		$filetype = $adb->query_result($res, 0, "filetype");
		updateDownloadCount($id);

		$fileidQuery = 'select attachmentsid from vtiger_seattachmentsrel where crmid = ?';
		$fileres = $adb->pquery($fileidQuery, array($id));
		$fileid = $adb->query_result($fileres, 0, 'attachmentsid');

		$filepathQuery = 'select path,name from vtiger_attachments where attachmentsid = ?';
		$fileres = $adb->pquery($filepathQuery, array($fileid));
		$filepath = $adb->query_result($fileres, 0, 'path');
		$filename = $adb->query_result($fileres, 0, 'name');
		$filename = decode_html($filename);

		$saved_filename = $fileid . "_" . $filename;
		$filenamewithpath = $filepath . $saved_filename;
		$filesize = filesize($filenamewithpath);
	} else {
		$query = 'select vtiger_attachments.*,vtiger_seattachmentsrel.* from vtiger_attachments inner join vtiger_seattachmentsrel on vtiger_seattachmentsrel.attachmentsid=vtiger_attachments.attachmentsid where vtiger_seattachmentsrel.crmid =?';

		$res = $adb->pquery($query, array($id));

		$filename = $adb->query_result($res, 0, 'name');
		$filename = decode_html($filename);
		$filepath = $adb->query_result($res, 0, 'path');
		$fileid = $adb->query_result($res, 0, 'attachmentsid');
		$filesize = filesize($filepath . $fileid . "_" . $filename);
		$filetype = $adb->query_result($res, 0, 'type');
		$filenamewithpath = $filepath . $fileid . '_' . $filename;
	}
	$output[0]['fileid'] = $fileid;
	$output[0]['filename'] = $filename;
	$output[0]['filetype'] = $filetype;
	$output[0]['filesize'] = $filesize;
	$output[0]['filecontents'] = base64_encode(file_get_contents($filenamewithpath));
	$log->debug("Exiting customer portal function get_filecontent_detail ");
	return $output;
}

/** Function that the client actually calls when a file is downloaded
 *
 */
function updateCount($id)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function updateCount");
	$result = updateDownloadCount($id);
	$log->debug("Entering customer portal function updateCount");
	return $result;
}

/**
 * Function to update the download count of a file
 */
function updateDownloadCount($id)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function updateDownloadCount");
	$updateDownloadCount = "UPDATE vtiger_notes SET filedownloadcount = filedownloadcount+1 WHERE notesid = ?";
	$countres = $adb->pquery($updateDownloadCount, array($id));
	$log->debug("Entering customer portal function updateDownloadCount");
	return true;
}

function get_inventory_products($id, $module, $customerid, $sessionid)
{
	require_once('include/utils/UserInfoUtil.php');
	require_once('include/utils/utils.php');
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$site_URL = AppConfig::main('site_URL');

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$isPermitted = check_permission($customerid, $module, $id);
	if ($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}
	return [];
}
/* Function to get contactid's and account's product details'
 *
 */

function get_product_list_values($id, $modulename, $sessionid, $only_mine = 'true')
{
	require_once('modules/Products/Products.php');
	require_once('include/utils/UserInfoUtil.php');
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_product_list_values ..");
	$check = checkModuleActive($modulename);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	$entity_ids_list = [];
	$show_all = show_all($modulename);

	if (!validateSession($id, $sessionid))
		return null;

	if ($only_mine == 'true' || $show_all == 'false') {
		array_push($entity_ids_list, $id);
	} else {
		$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" && vtiger_crmentity.deleted = 0 " .
			" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?)  && parentid != 0) || contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id, $id));
		$no_of_cont = $adb->num_rows($contactres);
		for ($i = 0; $i < $no_of_cont; $i++) {
			$cont_id = $adb->query_result($contactres, $i, 'contactid');
			$acc_id = $adb->query_result($contactres, $i, 'parentid');
			if (!in_array($cont_id, $entity_ids_list))
				$entity_ids_list[] = $cont_id;
			if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
				$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new Products();
	$focus->filterInactiveFields('Products');
	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
		}
	}
	$fields_list['Related To'] = 'entityid';
	$query = [];
	$params = [];
	$query[] = sprintf("SELECT vtiger_products.*,vtiger_seproductsrel.crmid as entityid, vtiger_seproductsrel.setype FROM vtiger_products
		INNER JOIN vtiger_crmentity on vtiger_products.productid = vtiger_crmentity.crmid
		LEFT JOIN vtiger_seproductsrel on vtiger_seproductsrel.productid = vtiger_products.productid
		WHERE vtiger_seproductsrel.crmid in (%s) and vtiger_crmentity.deleted = 0 ", generateQuestionMarks($entity_ids_list));
	$params[] = array($entity_ids_list);

	$fieldValuesToRound = array('unit_price', 'weight', 'commissionrate', 'qtyinstock');
	for ($k = 0; $k < count($query); $k++) {
		$res[$k] = $adb->pquery($query[$k], $params[$k]);
		$noofdata[$k] = $adb->num_rows($res[$k]);
		if ($noofdata[$k] == 0)
			$output[$k][$modulename]['data'] = '';
		for ($j = 0; $j < $noofdata[$k]; $j++) {
			$i = 0;
			foreach ($fields_list as $fieldlabel => $fieldname) {
				$fieldper = getFieldVisibilityPermission('Products', $current_user->id, $fieldname);
				if ($fieldper == '1' && $fieldname != 'entityid') {
					continue;
				}
				$output[$k][$modulename]['head'][0][$i]['fielddata'] = Vtiger_Language_Handler::getTranslatedString($fieldlabel, 'Products', vglobal('default_language'));
				$fieldvalue = $adb->query_result($res[$k], $j, $fieldname);
				$fieldid = $adb->query_result($res[$k], $j, 'productid');

				if (in_array($fieldname, $fieldValuesToRound)) {
					$fieldvalue = round($fieldvalue, 2);
				}
				if ($fieldname == 'entityid') {
					$crmid = $fieldvalue;
					$module = $adb->query_result($res[$k], $j, 'setype');
					if ($crmid != '' && $module != '') {
						$fieldvalues = getEntityName($module, array($crmid));
						if ($module == 'Contacts')
							$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
						elseif ($module == 'Accounts')
							$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
					} else {
						$fieldvalue = '';
					}
				}

				if ($fieldname == 'productname')
					$fieldvalue = '<a href="index.php?module=Products&action=index&productid=' . $fieldid . '">' . $fieldvalue . '</a>';

				if ($fieldname == 'unit_price') {
					$sym = getCurrencySymbol($res[$k], $j, 'currency_id');
					$fieldvalue = $sym . $fieldvalue;
				}
				$output[$k][$modulename]['data'][$j][$i]['fielddata'] = $fieldvalue;
				$i++;
			}
		}
	}
	$log->debug("Exiting function get_product_list_values.....");
	return $output;
}
/* function used to get details of tickets,documents,Products,Contacts,Accounts
 * 	@param int $id - id of invoice or notes
 * 	return string $message - Account informations will be returned from :Accountdetails table
 */

function get_details($id, $module, $customerid, $sessionid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	require_once('include/utils/utils.php');
	require_once('include/utils/UserInfoUtil.php');
	$log->debug("Entering customer portal function get_details ..");

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$isPermitted = check_permission($customerid, $module, $id);
	if ($isPermitted == false) {
		return array("#NOT AUTHORIZED#");
	}

	if (!validateSession($customerid, $sessionid))
		return null;

	$params = array($id);
	if ($module == 'Documents') {
		$result = $adb->pquery('SELECT fieldparams FROM vtiger_field WHERE columnname = ? && tablename = ?', ['folderid', 'vtiger_notes']);
		$tree = $adb->query_result($result, 0, 'fieldparams');

		$params[] = $tree;
		$query = sprintf("SELECT
			vtiger_notes.*,vtiger_crmentity.*, vtiger_trees_templates_data.label as foldername,vtiger_notescf.*
			FROM vtiger_notes
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid
			LEFT JOIN vtiger_trees_templates_data ON vtiger_trees_templates_data.tree = vtiger_notes.folderid
			LEFT JOIN vtiger_notescf ON vtiger_notescf.notesid = vtiger_notes.notesid
			WHERE vtiger_notes.notesid=(%s) && vtiger_trees_templates_data.templateid = ? && vtiger_crmentity.deleted=0", generateQuestionMarks($id));
	} else if ($module == 'HelpDesk') {
		$query = sprintf("SELECT
			vtiger_troubletickets.*,vtiger_crmentity.smownerid,vtiger_crmentity.createdtime,vtiger_crmentity.modifiedtime,vtiger_crmentity.attention,
			vtiger_ticketcf.*,vtiger_crmentity.description  FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
			INNER JOIN vtiger_ticketcf
				ON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
			WHERE (vtiger_troubletickets.ticketid=(%s) && vtiger_crmentity.deleted = 0)", generateQuestionMarks($id));
	} else if ($module == 'Services') {
		$result = $adb->pquery('SELECT fieldparams FROM vtiger_field WHERE columnname = ? && tablename = ?', ['pscategory', 'vtiger_service']);
		$tree = $adb->query_result($result, 0, 'fieldparams');

		$query = sprintf("SELECT vtiger_service.*,vtiger_crmentity.*,vtiger_servicecf.*, vtiger_trees_templates_data.label as pscategory
			FROM vtiger_service
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_service.serviceid && vtiger_crmentity.deleted = 0
			LEFT JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
			LEFT JOIN vtiger_trees_templates_data ON vtiger_trees_templates_data.tree = vtiger_service.pscategory
			WHERE vtiger_service.serviceid= (%s)", generateQuestionMarks($id));
	} else if ($module == 'Contacts') {
		$query = "SELECT vtiger_contactdetails.*,vtiger_contactaddress.*,vtiger_contactsubdetails.*,vtiger_contactscf.*" .
			" ,vtiger_crmentity.*,vtiger_customerdetails.*
		 	FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactsubdetails
				ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
			INNER JOIN vtiger_contactscf
				ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid
			LEFT JOIN vtiger_customerdetails
				ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
			WHERE vtiger_contactdetails.contactid = (" . generateQuestionMarks($id) . ") && vtiger_crmentity.deleted = 0";
	} else if ($module == 'Accounts') {
		$query = "SELECT vtiger_account.*,vtiger_accountaddress.*,vtiger_accountscf.*,
			vtiger_crmentity.* FROM vtiger_account
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_account.accountid
			INNER JOIN vtiger_accountaddress
				ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid
			INNER JOIN vtiger_accountscf
				ON vtiger_account.accountid = vtiger_accountscf.accountid" .
			" WHERE vtiger_account.accountid = (" . generateQuestionMarks($id) . ") && vtiger_crmentity.deleted = 0";
	} else if ($module == 'Products') {
		$query = sprintf("SELECT vtiger_products.*,vtiger_productcf.*,vtiger_crmentity.* " .
			"FROM vtiger_products " .
			"INNER JOIN vtiger_crmentity " .
			"ON vtiger_crmentity.crmid = vtiger_products.productid " .
			"LEFT JOIN vtiger_productcf " .
			"ON vtiger_productcf.productid = vtiger_products.productid " .
			"LEFT JOIN vtiger_vendor
			ON vtiger_vendor.vendorid = vtiger_products.vendor_id " .
			"WHERE vtiger_products.productid = (%s) && vtiger_crmentity.deleted = 0", generateQuestionMarks($id));
	} else if ($module == 'Assets') {
		$query = sprintf("SELECT vtiger_assets.*, vtiger_assetscf.*, vtiger_crmentity.*
		FROM vtiger_assets
		INNER JOIN vtiger_crmentity
		ON vtiger_assets.assetsid = vtiger_crmentity.crmid
		INNER JOIN vtiger_assetscf
		ON vtiger_assetscf.assetsid = vtiger_assets.assetsid
		WHERE vtiger_crmentity.deleted = 0 && vtiger_assets.assetsid = (%s)", generateQuestionMarks($id));
	} else if ($module == 'Project') {
		$query = "SELECT vtiger_project.*, vtiger_projectcf.*, vtiger_crmentity.*
					FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
					LEFT JOIN vtiger_projectcf ON vtiger_projectcf.projectid = vtiger_project.projectid
					WHERE vtiger_project.projectid = ? && vtiger_crmentity.deleted = 0";
	} else if ($module == 'ProjectMilestone') {
		$query = "SELECT vtiger_projectmilestone . * , vtiger_projectmilestonecf . * , vtiger_crmentity . * 
					FROM vtiger_projectmilestone
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projectmilestone.projectmilestoneid
					LEFT JOIN vtiger_projectmilestonecf ON vtiger_projectmilestonecf.projectmilestoneid = vtiger_projectmilestone.projectmilestoneid
					WHERE vtiger_projectmilestone.projectmilestoneid = ? && vtiger_crmentity.deleted =0";
	} else if ($module == 'ProjectTask') {
		$query = "SELECT vtiger_projecttask.*, vtiger_projecttaskcf.*, vtiger_crmentity.*
					FROM vtiger_projecttask
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid
					LEFT JOIN vtiger_projecttaskcf ON vtiger_projecttaskcf.projecttaskid = vtiger_projecttask.projecttaskid
					WHERE vtiger_projecttask.projecttaskid = ? && vtiger_crmentity.deleted = 0";
	}

	$res = $adb->pquery($query, $params);

	$fieldquery = "SELECT fieldname,columnname,fieldlabel,blocklabel,uitype FROM vtiger_field
		INNER JOIN  vtiger_blocks on vtiger_blocks.blockid=vtiger_field.block WHERE vtiger_field.tabid = ? && displaytype in (1,2,4,10)
		ORDER BY vtiger_field.block,vtiger_field.sequence";

	$fieldres = $adb->pquery($fieldquery, array(\includes\Modules::getModuleId($module)));
	$nooffields = $adb->num_rows($fieldres);

	// Dummy instance to make sure column fields are initialized for futher processing
	$focus = CRMEntity::getInstance($module);

	for ($i = 0; $i < $nooffields; $i++) {
		$columnname = $adb->query_result($fieldres, $i, 'columnname');
		$fieldname = $adb->query_result($fieldres, $i, 'fieldname');
		$fieldid = $adb->query_result($fieldres, $i, 'fieldid');
		$blockid = $adb->query_result($fieldres, $i, 'block');
		$uitype = $adb->query_result($fieldres, $i, 'uitype');
		$blocklabel = $adb->query_result($fieldres, $i, 'blocklabel');
		$blockname = Vtiger_Language_Handler::getTranslatedString($blocklabel, $module, vglobal('default_language'));
		if ($blocklabel == 'LBL_COMMENTS' || $blocklabel == 'LBL_IMAGE_INFORMATION') { // the comments block of tickets is hardcoded in customer portal,get_ticket_comments is used for it
			continue;
		}
		if ($uitype == 83) { //for taxclass in products and services
			continue;
		}
		$fieldper = getFieldVisibilityPermission($module, $current_user->id, $fieldname);
		if ($fieldper == '1') {
			continue;
		}

		$fieldlabel = Vtiger_Language_Handler::getTranslatedString($adb->query_result($fieldres, $i, 'fieldlabel'), $module, vglobal('default_language'));
		$fieldvalue = $adb->query_result($res, 0, $columnname);

		$output[0][$module][$i]['fieldlabel'] = $fieldlabel;
		$output[0][$module][$i]['blockname'] = $blockname;
		if ($columnname == 'title' || $columnname == 'description' || $columnname == 'attention' || $columnname == 'solution') {
			$fieldvalue = decode_html($fieldvalue);
		}
		if ($uitype == 10 && $fieldvalue == 0) {
			$fieldvalue = '';
		}
		if ($uitype == 71 || $uitype == 72) {
			$fieldvalue = number_format($fieldvalue, 5, '.', '');
		}
		if ($uitype == 56) {
			if ($fieldvalue == 1) {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString('LBL_YES', $module, vglobal('default_language'));
			} else {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString('LBL_NO', $module, vglobal('default_language'));
			}
		}
		if ($columnname == 'parent_id' || $columnname == 'contactid' || $columnname == 'accountid' || $fieldname == 'account_id' || $fieldname == 'contact_id' || $columnname == 'linktoaccountscontacts') {
			$crmid = $fieldvalue;
			$modulename = \vtlib\Functions::getCRMRecordType($crmid);
			if ($crmid != '' && $modulename != '') {
				$fieldvalues = getEntityName($modulename, array($crmid));
				if ($modulename == 'Contacts')
					$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
				elseif ($modulename == 'Accounts')
					$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
				else
					$fieldvalue = $fieldvalues[$crmid];
			} else {
				$fieldvalue = '';
			}
		}

		if ($module == 'Services') {
			if ($fieldname == 'pscategory' && $fieldvalue != '') {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, $module, vglobal('default_language'));
			}
		}
		if ($module == 'Documents') {
			$fieldid = $adb->query_result($res, 0, 'notesid');
			$filename = $fieldvalue;
			$folderid = $adb->query_result($res, 0, 'folderid');
			$filestatus = $adb->query_result($res, 0, 'filestatus');
			$filetype = $adb->query_result($res, 0, 'filelocationtype');
			if ($fieldname == 'filename') {
				if ($filestatus == 1) {
					if ($filetype == 'I') {
						$fieldvalue = '<a href="index.php?downloadfile=true&folderid=' . $folderid . '&filename=' . $filename . '&module=Documents&action=index&id=' . $fieldid . '" >' . $fieldvalue . '</a>';
					} elseif ($filetype == 'E') {
						$fieldvalue = '<a target="_blank" href="' . $filename . '" onclick = "updateCount(' . $fieldid . ');">' . $filename . '</a>';
					}
				}
			}
			if ($fieldname == 'folderid') {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString($adb->query_result($res, 0, 'foldername'), $module, vglobal('default_language'));
			}
			if ($fieldname == 'filesize') {
				if ($filetype == 'I') {
					$fieldvalue = $fieldvalue . ' B';
				} elseif ($filetype == 'E') {
					$fieldvalue = '--';
				}
			}
			if ($fieldname == 'filelocationtype') {
				if ($fieldvalue == 'I') {
					$fieldvalue = Vtiger_Language_Handler::getTranslatedString('LBL_INTERNAL', $module, vglobal('default_language'));
				} elseif ($fieldvalue == 'E') {
					$fieldvalue = Vtiger_Language_Handler::getTranslatedString('LBL_EXTERNAL', $module, vglobal('default_language'));
				} else {
					$fieldvalue = '---';
				}
			}
		}
		if ($columnname == 'product_id') {
			$fieldvalues = getEntityName('Products', array($fieldvalue));
			$fieldvalue = '<a href="index.php?module=Products&action=index&productid=' . $fieldvalue . '">' . $fieldvalues[$fieldvalue] . '</a>';
		}
		if ($module == 'Products') {
			if ($fieldname == 'vendor_id') {
				$fieldvalue = get_vendor_name($fieldvalue);
			}
		}
		if ($module == 'Assets') {
			if ($fieldname == 'account') {
				$accountid = $adb->query_result($res, 0, 'account');
				$accountres = $adb->pquery("select vtiger_account.accountname from vtiger_account where accountid=?", array($accountid));
				$accountname = $adb->query_result($accountres, 0, 'accountname');
				$fieldvalue = $accountname;
			}
			if ($fieldname == 'product') {
				$productid = $adb->query_result($res, 0, 'product');
				$productres = $adb->pquery("select vtiger_products.productname from vtiger_products where productid=?", array($productid));
				$productname = $adb->query_result($productres, 0, 'productname');
				$fieldvalue = $productname;
			}
		}
		if (in_array($uitype, array('15', '16', '120'))) {
			$output[0][$module][$i]['orgfieldvalue'] = $fieldvalue;
			$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, $module, vglobal('default_language'));
		}
		if (in_array($uitype, array('10'))) {
			$fieldvalue = vtlib\Functions::getCRMRecordLabel($fieldvalue);
		}
		if (in_array($uitype, array('53', '52', '77'))) {
			$fieldvalue = vtlib\Functions::getOwnerRecordLabel($fieldvalue);
		}
		if ($fieldname == 'unit_price') {
			$sym = getCurrencySymbol($res, 0, 'currency_id');
			$fieldvalue = round($fieldvalue, 2);
			$fieldvalue = $sym . $fieldvalue;
		}
		$output[0][$module][$i]['fieldvalue'] = $fieldvalue;
	}

	if ($module == 'HelpDesk') {
		$ticketid = $adb->query_result($res, 0, 'ticketid');
		$sc_info = getRelatedServiceContracts($ticketid);
		if (!empty($sc_info)) {
			$modulename = 'ServiceContracts';
			$blocklable = Vtiger_Language_Handler::getTranslatedString('LBL_SERVICE_CONTRACT_INFORMATION', $modulename, vglobal('default_language'));
			$j = $i;
			for ($k = 0; $k < count($sc_info); $k++) {
				foreach ($sc_info[$k] as $label => $value) {
					$output[0][$module][$j]['fieldlabel'] = Vtiger_Language_Handler::getTranslatedString($label, $modulename, vglobal('default_language'));
					$output[0][$module][$j]['fieldvalue'] = $value;
					$output[0][$module][$j]['blockname'] = $blocklable;
					$j++;
				}
			}
		}
	}
	$log->debug("Existing customer portal function get_details ..");
	return $output;
}
/* Function to check the permission if the customer can see the recorde details
 * @params $customerid :: INT contact's Id
 * 			$module :: String modulename
 * 			$entityid :: INT Records Id
 */

function check_permission($customerid, $module, $entityid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function check_permission ..");
	$show_all = show_all($module);
	$allowed_contacts_and_accounts = [];
	$check = checkModuleActive($module);
	if ($check == false) {
		return false;
	}

	if ($show_all == 'false')
		$allowed_contacts_and_accounts[] = $customerid;
	else {

		$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" && vtiger_crmentity.deleted = 0 " .
			" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?) && parentid != 0) || contactid = ?";
		$contactres = $adb->pquery($contactquery, array($customerid, $customerid));
		$no_of_cont = $adb->num_rows($contactres);
		for ($i = 0; $i < $no_of_cont; $i++) {
			$cont_id = $adb->query_result($contactres, $i, 'contactid');
			$acc_id = $adb->query_result($contactres, $i, 'parentid');
			if (!in_array($cont_id, $allowed_contacts_and_accounts))
				$allowed_contacts_and_accounts[] = $cont_id;
			if (!in_array($acc_id, $allowed_contacts_and_accounts) && $acc_id != '0')
				$allowed_contacts_and_accounts[] = $acc_id;
		}
	}
	if (in_array($entityid, $allowed_contacts_and_accounts)) { //for contact's,if they are present in the allowed list then send true
		return true;
	}
	$faqquery = "select id from vtiger_faq";
	$faqids = $adb->pquery($faqquery, []);
	$no_of_faq = $adb->num_rows($faqids);
	for ($i = 0; $i < $no_of_faq; $i++) {
		$faq_id[] = $adb->query_result($faqids, $i, 'id');
	}
	switch ($module) {
		case 'Products' : $query = sprintf("SELECT vtiger_seproductsrel.productid FROM vtiger_seproductsrel
								INNER JOIN vtiger_crmentity
								ON vtiger_seproductsrel.productid=vtiger_crmentity.crmid
								WHERE vtiger_seproductsrel.crmid IN (%s)
									AND vtiger_crmentity.deleted=0
									AND vtiger_seproductsrel.productid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;
		case 'Documents' : $query = sprintf("SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_senotesrel.notesid && vtiger_crmentity.deleted = 0
									WHERE vtiger_senotesrel.crmid IN (%s)
									AND vtiger_senotesrel.notesid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			if (checkModuleActive('Project')) {
				$query = sprintf("SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
										INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_senotesrel.crmid
										WHERE vtiger_project.linktoaccountscontacts IN (%s)
										AND vtiger_senotesrel.notesid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
				$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
				if ($adb->num_rows($res) > 0) {
					return true;
				}
			}

			$query = sprintf("SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel
															INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_senotesrel.notesid && vtiger_crmentity.deleted = 0
															WHERE vtiger_senotesrel.crmid IN (%s)
															AND vtiger_senotesrel.notesid = ?", generateQuestionMarks($faq_id));
			$res = $adb->pquery($query, array($faq_id, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;

		case 'HelpDesk' : $query = sprintf("SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid && vtiger_crmentity.deleted = 0
									WHERE (vtiger_troubletickets.parent_id IN (%s) )
									AND vtiger_troubletickets.ticketid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}

			$query = sprintf("SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
									INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid || vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
									WHERE vtiger_crmentity.deleted = 0 AND
											(vtiger_crmentityrel.crmid IN
												(SELECT projectid FROM vtiger_project WHERE linktoaccountscontacts
													IN (%s) && vtiger_crmentityrel.relcrmid = $entityid)
											OR vtiger_crmentityrel.relcrmid IN
												(SELECT projectid FROM vtiger_project WHERE linktoaccountscontacts
													IN (%s) && vtiger_crmentityrel.crmid = $entityid)
										AND vtiger_troubletickets.ticketid = ?)", generateQuestionMarks($allowed_contacts_and_accounts), generateQuestionMarks($allowed_contacts_and_accounts));

			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}

			break;

		case 'Services' : $query = sprintf("SELECT vtiger_service.serviceid FROM vtiger_service
									INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid && vtiger_crmentity.deleted = 0
									LEFT JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid=vtiger_service.serviceid || vtiger_crmentityrel.crmid=vtiger_service.serviceid)
									WHERE (vtiger_crmentityrel.crmid IN (%s)  || " .
				"(vtiger_crmentityrel.relcrmid IN (%s) && vtiger_crmentityrel.module = 'Services'))
									AND vtiger_service.serviceid = ?", generateQuestionMarks($allowed_contacts_and_accounts), generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;

		case 'Accounts' : $query = "SELECT vtiger_account.accountid FROM vtiger_account " .
				"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid " .
				"INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.parentid = vtiger_account.accountid " .
				"WHERE vtiger_crmentity.deleted = 0 and vtiger_contactdetails.contactid = ? and vtiger_contactdetails.parentid = ?";
			$res = $adb->pquery($query, array($customerid, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;

		case 'Assets' : $query = "SELECT vtiger_assets.assetname FROM vtiger_assets
								INNER JOIN vtiger_crmentity ON  vtiger_assets.assetsid = vtiger_crmentity.crmid
								WHERE vtiger_crmentity.deleted = 0 and vtiger_assets.parent_id = ? ";
			$accountid = '';
			$accountRes = $adb->pquery("SELECT parentid FROM vtiger_contactdetails
								INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
								WHERE contactid = ? && deleted = 0", array($customerid));
			$accountRow = $adb->num_rows($accountRes);
			if ($accountRow) {
				$accountid = $adb->query_result($accountRes, 0, 'parentid');
			}
			$res = $adb->pquery($query, array($accountid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;

		case 'Project' :
			$query = sprintf("SELECT vtiger_project.projectid FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid && vtiger_crmentity.deleted = 0
					WHERE vtiger_project.linktoaccountscontacts IN (%s)
					AND vtiger_project.projectid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;
		case 'ProjectTask' :
			$query = sprintf("SELECT vtiger_projecttask.projecttaskid FROM vtiger_projecttask
					Left JOIN vtiger_project ON vtiger_project.projectid = vtiger_projecttask.projectid
					Left JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid && vtiger_crmentity.deleted = 0
					WHERE vtiger_project.linktoaccountscontacts IN (%s)
					AND vtiger_projecttask.projecttaskid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;
		case 'ProjectMilestone' :
			$query = sprintf("SELECT vtiger_projectmilestone.projectmilestoneid FROM vtiger_projectmilestone
					Left JOIN vtiger_project ON vtiger_project.projectid = vtiger_projectmilestone.projectid
					Left JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid && vtiger_crmentity.deleted = 0
					WHERE vtiger_project.linktoaccountscontacts IN (%s)
					AND vtiger_projectmilestone.projectmilestoneid = ?", generateQuestionMarks($allowed_contacts_and_accounts));
			$res = $adb->pquery($query, array($allowed_contacts_and_accounts, $entityid));
			if ($adb->num_rows($res) > 0) {
				return true;
			}
			break;
	}
	return false;
	$log->debug("Exiting customerportal function check_permission ..");
}
/* Function to get related Documents for faq
 *  @params $id :: INT parent's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */

function get_documents($id, $module, $customerid, $sessionid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_documents ..");
	$check = checkModuleActive($module);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}
	$fields_list = array(
		'title' => 'Title',
		'filename' => 'FileName',
		'createdtime' => 'Created Time');

	if (!validateSession($customerid, $sessionid))
		return null;

	$query = "select vtiger_notes.title,'Documents' ActivityType, vtiger_notes.filename,
		crm2.createdtime,vtiger_notes.notesid,vtiger_notes.folderid,
		vtiger_notes.notecontent description, vtiger_users.user_name, vtiger_notes.filelocationtype
		from vtiger_notes
		LEFT join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid
		INNER join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_senotesrel.crmid
		LEFT join vtiger_crmentity crm2 on crm2.crmid=vtiger_notes.notesid and crm2.deleted=0
		LEFT JOIN vtiger_groups
		ON vtiger_groups.groupid = vtiger_crmentity.smownerid
		LEFT join vtiger_users on crm2.smownerid= vtiger_users.id
		where vtiger_crmentity.crmid=?";
	$res = $adb->pquery($query, array($id));
	$noofdata = $adb->num_rows($res);
	for ($j = 0; $j < $noofdata; $j++) {
		$i = 0;
		foreach ($fields_list as $fieldname => $fieldlabel) {
			$output[0][$module]['head'][0][$i]['fielddata'] = $fieldlabel; //$adb->query_result($fieldres,$i,'fieldlabel');
			$fieldvalue = $adb->query_result($res, $j, $fieldname);
			if ($fieldname == 'title') {
				$fieldid = $adb->query_result($res, $j, 'notesid');
				$filename = $fieldvalue;
				$fieldvalue = '<a href="index.php?&module=Documents&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
			}
			if ($fieldname == 'filename') {
				$fieldid = $adb->query_result($res, $j, 'notesid');
				$filename = $fieldvalue;
				$folderid = $adb->query_result($res, $j, 'folderid');
				$filetype = $adb->query_result($res, $j, 'filelocationtype');
				if ($filetype == 'I') {
					$fieldvalue = '<a href="index.php?&downloadfile=true&folderid=' . $folderid . '&filename=' . $filename . '&module=Documents&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';
				} else {
					$fieldvalue = '<a target="_blank" href="' . $filename . '">' . $filename . '</a>';
				}
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_faq_document ..");
	return $output;
}
/* Function to get related projecttasks/projectmilestones for a Project
 *  @params $id :: INT Project's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */

function get_project_components($id, $module, $customerid, $sessionid)
{
	\vtlib\Deprecated::checkFileAccessForInclusion("modules/$module/$module.php");
	require_once("modules/$module/$module.php");
	require_once('include/utils/UserInfoUtil.php');

	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_project_components ..");
	$check = checkModuleActive($module);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}

	if (!validateSession($customerid, $sessionid))
		return null;

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$focus = new $module();
	$focus->filterInactiveFields($module);
	$componentfieldVisibilityByColumn = [];
	$fields_list = [];

	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
			$componentfieldVisibilityByColumn[$fieldname] = getColumnVisibilityPermission($current_user->id, $fieldname, $module);
		}
	}

	if ($module == 'ProjectTask') {
		$query = "SELECT vtiger_projecttask.*, vtiger_crmentity.smownerid
				FROM vtiger_projecttask
				INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_projecttask.projectid && vtiger_project.projectid = ?
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid && vtiger_crmentity.deleted = 0";
	} elseif ($module == 'ProjectMilestone') {
		$query = "SELECT vtiger_projectmilestone.*, vtiger_crmentity.smownerid
				FROM vtiger_projectmilestone
				INNER JOIN vtiger_project ON vtiger_project.projectid = vtiger_projectmilestone.projectid && vtiger_project.projectid = ?
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projectmilestone.projectmilestoneid && vtiger_crmentity.deleted = 0";
	}

	$res = $adb->pquery($query, array(vtlib_purify($id)));
	$noofdata = $adb->num_rows($res);

	for ($j = 0; $j < $noofdata; ++$j) {
		$i = 0;
		foreach ($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $componentfieldVisibilityByColumn[$fieldname];
			if ($fieldper == '1') {
				continue;
			}
			$output[0][$module]['head'][0][$i]['fielddata'] = Vtiger_Language_Handler::getTranslatedString($fieldlabel, $module, vglobal('default_language'));
			$projectmilestoneid = $adb->query_result($res, $j, 'projectmilestoneid');
			$fieldvalue = $adb->query_result($res, $j, $fieldname);
			$projecttaskid = $adb->query_result($res, $j, 'projecttaskid');
			if ($fieldname == 'projecttaskname') {
				$fieldvalue = '<a href="index.php?module=ProjectTask&action=index&id=' . $projecttaskid . '">' . $fieldvalue . '</a>';
			}
			if ($fieldname == 'projectmilestonename') {
				$fieldvalue = '<a href="index.php?module=ProjectMilestone&action=index&id=' . $projectmilestoneid . '">' . $fieldvalue . '</a>';
			}
			if ($fieldname == 'smownerid') {
				$fieldvalue = \includes\fields\Owner::getLabel($fieldvalue);
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_project_components ..");
	return $output;
}
/* Function to get related tickets for a Project
 *  @params $id :: INT Project's Id
 * 			$module :: String modulename
 * 			$customerid :: INT contact's Id'
 */

function get_project_tickets($id, $module, $customerid, $sessionid)
{
	require_once('modules/HelpDesk/HelpDesk.php');
	require_once('include/utils/UserInfoUtil.php');

	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_project_tickets ..");
	$check = checkModuleActive($module);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}

	if (!validateSession($customerid, $sessionid))
		return null;

	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);

	$focus = new HelpDesk();
	$focus->filterInactiveFields('HelpDesk');
	$TicketsfieldVisibilityByColumn = [];
	$fields_list = [];
	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
			$TicketsfieldVisibilityByColumn[$fieldname] = getColumnVisibilityPermission($current_user->id, $fieldname, 'HelpDesk');
		}
	}

	$query = "SELECT vtiger_troubletickets.*, vtiger_crmentity.smownerid FROM vtiger_troubletickets
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
		INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid || vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
		WHERE vtiger_crmentity.deleted = 0 && (vtiger_crmentityrel.crmid = ? || vtiger_crmentityrel.relcrmid = ?)";

	$params = array($id, $id);
	$res = $adb->pquery($query, $params);
	$noofdata = $adb->num_rows($res);

	for ($j = 0; $j < $noofdata; $j++) {
		$i = 0;
		foreach ($fields_list as $fieldlabel => $fieldname) {
			$fieldper = $TicketsfieldVisibilityByColumn[$fieldname]; //in troubletickets the list_fields has columns so we call this API
			if ($fieldper == '1') {
				continue;
			}
			$output[0][$module]['head'][0][$i]['fielddata'] = Vtiger_Language_Handler::getTranslatedString($fieldlabel, 'HelpDesk', vglobal('default_language'));
			$fieldvalue = $adb->query_result($res, $j, $fieldname);
			$ticketid = $adb->query_result($res, $j, 'ticketid');
			if ($fieldname == 'title') {
				$fieldvalue = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid=' . $ticketid . '">' . $fieldvalue . '</a>';
			}
			if ($fieldname == 'parent_id' || $fieldname == 'contact_id') {
				$crmid = $fieldvalue;
				$entitymodule = \vtlib\Functions::getCRMRecordType($crmid);
				if ($crmid != '' && $entitymodule != '') {
					$fieldvalues = getEntityName($entitymodule, array($crmid));
					if ($entitymodule == 'Contacts')
						$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
					elseif ($entitymodule == 'Accounts')
						$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
				} else {
					$fieldvalue = '';
				}
			}
			if ($fieldname == 'smownerid') {
				$fieldvalue = \includes\fields\Owner::getLabel($fieldvalue);
			}
			if ($fieldlabel == 'Status') {
				$fieldvalue = Vtiger_Language_Handler::getTranslatedString($fieldvalue, 'HelpDesk', vglobal('default_language'));
			}
			$output[1][$module]['data'][$j][$i]['fielddata'] = $fieldvalue;
			$i++;
		}
	}
	$log->debug("Exiting customerportal function  get_project_tickets ..");
	return $output;
}
/* Function to get contactid's and account's product details'
 *
 */

function get_service_list_values($id, $modulename, $sessionid, $only_mine = 'true')
{
	require_once('modules/Services/Services.php');
	require_once('include/utils/UserInfoUtil.php');
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal Function get_service_list_values");
	$check = checkModuleActive($modulename);
	if ($check == false) {
		return array("#MODULE INACTIVE#");
	}
	$user = new Users();
	$userid = getPortalUserid();
	$current_user = $user->retrieveCurrentUserInfoFromFile($userid);
	//To avoid SQL injection we are type casting as well as bound the id variable
	$id = (int) vtlib_purify($id);
	$entity_ids_list = [];
	$show_all = show_all($modulename);

	if (!validateSession($id, $sessionid))
		return null;

	if ($only_mine == 'true' || $show_all == 'false') {
		array_push($entity_ids_list, $id);
	} else {
		$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
			" && vtiger_crmentity.deleted = 0 " .
			" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?)  && parentid != 0) || contactid = ?";
		$contactres = $adb->pquery($contactquery, array($id, $id));
		$no_of_cont = $adb->num_rows($contactres);
		for ($i = 0; $i < $no_of_cont; $i++) {
			$cont_id = $adb->query_result($contactres, $i, 'contactid');
			$acc_id = $adb->query_result($contactres, $i, 'parentid');
			if (!in_array($cont_id, $entity_ids_list))
				$entity_ids_list[] = $cont_id;
			if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
				$entity_ids_list[] = $acc_id;
		}
	}

	$focus = new Services();
	$focus->filterInactiveFields('Services');
	foreach ($focus->list_fields as $fieldlabel => $values) {
		foreach ($values as $table => $fieldname) {
			$fields_list[$fieldlabel] = $fieldname;
		}
	}
	$fields_list['Related To'] = 'entityid';
	$query = [];
	$params = [];

	$query[] = "select vtiger_service.*," .
		"case when vtiger_crmentityrel.crmid != vtiger_service.serviceid then vtiger_crmentityrel.crmid else vtiger_crmentityrel.relcrmid end as entityid, " .
		"'' as setype from vtiger_service " .
		"inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_service.serviceid " .
		"left join vtiger_crmentityrel on (vtiger_crmentityrel.relcrmid=vtiger_service.serviceid or vtiger_crmentityrel.crmid=vtiger_service.serviceid) " .
		"where vtiger_crmentity.deleted = 0 and " .
		"( vtiger_crmentityrel.crmid in (" . generateQuestionMarks($entity_ids_list) . ") || " .
		"(vtiger_crmentityrel.relcrmid in (" . generateQuestionMarks($entity_ids_list) . ") && vtiger_crmentityrel.module = 'Services')" .
		")";

	$params[] = array($entity_ids_list, $entity_ids_list);

	$ServicesfieldVisibilityPermissions = [];
	foreach ($fields_list as $fieldlabel => $fieldname) {
		$ServicesfieldVisibilityPermissions[$fieldname] = getFieldVisibilityPermission('Services', $current_user->id, $fieldname);
	}

	$fieldValuesToRound = array('unit_price', 'commissionrate');

	for ($k = 0; $k < count($query); $k++) {
		$res[$k] = $adb->pquery($query[$k], $params[$k]);
		$noofdata[$k] = $adb->num_rows($res[$k]);
		if ($noofdata[$k] == 0) {
			$output[$k][$modulename]['data'] = '';
		}
		for ($j = 0; $j < $noofdata[$k]; $j++) {
			$i = 0;
			foreach ($fields_list as $fieldlabel => $fieldname) {
				$fieldper = $ServicesfieldVisibilityPermissions[$fieldname];
				if ($fieldper == '1' && $fieldname != 'entityid') {
					continue;
				}
				$output[$k][$modulename]['head'][0][$i]['fielddata'] = Vtiger_Language_Handler::getTranslatedString($fieldlabel, 'Services', vglobal('default_language'));
				$fieldvalue = $adb->query_result($res[$k], $j, $fieldname);
				$fieldid = $adb->query_result($res[$k], $j, 'serviceid');

				if (in_array($fieldname, $fieldValuesToRound)) {
					$fieldvalue = round($fieldvalue, 2);
				}
				if ($fieldname == 'entityid') {
					$crmid = $fieldvalue;
					$module = $adb->query_result($res[$k], $j, 'setype');
					if ($module == '') {
						$module = $adb->query_result($adb->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid = ?", array($crmid)), 0, 'setype');
					}
					if ($crmid != '' && $module != '') {
						$fieldvalues = getEntityName($module, array($crmid));
						if ($module == 'Contacts')
							$fieldvalue = '<a href="index.php?module=Contacts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
						elseif ($module == 'Accounts')
							$fieldvalue = '<a href="index.php?module=Accounts&action=index&id=' . $crmid . '">' . $fieldvalues[$crmid] . '</a>';
					} else {
						$fieldvalue = '';
					}
				}

				if ($fieldname == 'servicename')
					$fieldvalue = '<a href="index.php?module=Services&action=index&id=' . $fieldid . '">' . $fieldvalue . '</a>';

				if ($fieldname == 'unit_price') {
					$sym = getCurrencySymbol($res[$k], $j, 'currency_id');
					$fieldvalue = $sym . $fieldvalue;
				}
				$output[$k][$modulename]['data'][$j][$i]['fielddata'] = $fieldvalue;
				$i++;
			}
		}
	}
	$log->debug("Exiting customerportal function get_product_list_values.....");
	return $output;
}
/* Function to get the list of modules allowed for customer portal
 */

function get_modules($id)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal Function get_modules");
	getServerSessionId($id);
	// Check if information is available in cache?
	$modules = Vtiger_Soap_YetiPortal::lookupAllowedModules();
	if ($modules === false) {
		$modules = [];

		$query = $adb->pquery("SELECT vtiger_customerportal_tabs.* FROM vtiger_customerportal_tabs
			INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_customerportal_tabs.tabid
			WHERE vtiger_tab.presence = 0 && vtiger_customerportal_tabs.visible = 1", []);
		$norows = $adb->num_rows($query);
		if ($norows) {
			while ($resultrow = $adb->fetch_array($query)) {
				$name = getTabModuleName($resultrow['tabid']);
				$modules[(int) $resultrow['sequence']] = array('name' => $name, 'translated_name' => Vtiger_Language_Handler::getTranslatedString($name, $name, vglobal('default_language')));
			}
			ksort($modules); // Order via SQL might cost us, so handling it ourselves in this case
		}
		Vtiger_Soap_YetiPortal::updateAllowedModules($modules);
	}
	$log->debug("Exiting customerportal function get_modules");
	return $modules;
}
/* Function to check if the module has the permission to show the related contact's and Account's information
 */

function show_all($module)
{

	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal Function show_all");
	$tabid = \includes\Modules::getModuleId($module);
	if ($module == 'Tickets') {
		$tabid = \includes\Modules::getModuleId('HelpDesk');
	}
	$query = $adb->pquery("SELECT prefvalue from vtiger_customerportal_prefs where tabid = ?", array($tabid));
	$norows = $adb->num_rows($query);
	if ($norows > 0) {
		if ($adb->query_result($query, 0, 'prefvalue') == 1) {
			return 'true';
		} else {
			return 'false';
		}
	} else {
		return 'false';
	}
	$log->debug("Exiting customerportal function show_all");
}
/* Function to get ServiceContracts information in the tickets module if the ticket is related to ServiceContracts
 */

function getRelatedServiceContracts($crmid)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function getRelatedServiceContracts");
	$module = 'ServiceContracts';
	$sc_info = [];
	if (\includes\Modules::isModuleActive($module) !== true) {
		return $sc_info;
	}
	$query = "SELECT * FROM vtiger_servicecontracts " .
		"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid && vtiger_crmentity.deleted = 0 " .
		"LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_servicecontracts.servicecontractsid " .
		"WHERE (vtiger_crmentityrel.relcrmid = ? and vtiger_crmentityrel.module= 'ServiceContracts')";

	$res = $adb->pquery($query, array($crmid));
	$rows = $adb->num_rows($res);
	for ($i = 0; $i < $rows; $i++) {
		$sc_info[$i]['Subject'] = $adb->query_result($res, $i, 'subject');
		$sc_info[$i]['Used Units'] = $adb->query_result($res, $i, 'used_units');
		$sc_info[$i]['Total Units'] = $adb->query_result($res, $i, 'total_units');
		$sc_info[$i]['Available Units'] = $adb->query_result($res, $i, 'total_units') - $adb->query_result($res, $i, 'used_units');
	}
	return $sc_info;
	$log->debug("Exiting customerportal function getRelatedServiceContracts");
}

/**     function to get summary widgets
 * *      @param int $id - Contact id
 * *      return array
 * */
function get_summary_widgets($id, $type)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function get_summary_widgets");
	$session = getServerSession($id);
	$output = $allowed_contacts_and_accounts = [];
	$contactquery = "SELECT contactid, parentid FROM vtiger_contactdetails " .
		" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid" .
		" && vtiger_crmentity.deleted = 0 " .
		" WHERE (parentid = (SELECT parentid FROM vtiger_contactdetails WHERE contactid = ?) && parentid != 0) || contactid = ?";
	$contactres = $adb->pquery($contactquery, array($id, $id));
	$no_of_cont = $adb->num_rows($contactres);
	for ($i = 0; $i < $no_of_cont; $i++) {
		$cont_id = $adb->query_result($contactres, $i, 'contactid');
		$acc_id = $adb->query_result($contactres, $i, 'parentid');

		if (!in_array($cont_id, $allowed_contacts_and_accounts))
			$allowed_contacts_and_accounts[] = $cont_id;
		if (!in_array($acc_id, $allowed_contacts_and_accounts) && $acc_id != '0')
			$allowed_contacts_and_accounts[] = $acc_id;
	}

	if (!in_array($id, $allowed_contacts_and_accounts)) { //for contact's,if they are present in the allowed list then send true
		return 'LBL_NO_PERMISSION_FOR_WIDGETS';
	}

	$query = "SELECT vtiger_troubletickets.*, vtiger_crmentity.smownerid,vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, '' AS setype
		FROM vtiger_troubletickets
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
		WHERE vtiger_crmentity.deleted = 0";
	if ($acc_id) {
		$sql .= " && vtiger_troubletickets.parent_id = $acc_id ";
	}

	if (in_array('OpenTickets', $type) || in_array('all', $type)) {
		$sql = 'SELECT count(*) AS count, case when ( concat(vtiger_users.first_name, " " ,vtiger_users.last_name)  not like "") then
			concat(vtiger_users.first_name, " " ,vtiger_users.last_name) else vtiger_groups.groupname end as name, vtiger_users.id as id 
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 && vtiger_troubletickets.status in ("Open","In Progress","Wait For Response") ';
		if ($acc_id) {
			$sql .= " && vtiger_troubletickets.parent_id = $acc_id ";
		}
		$sql .= ' GROUP BY smownerid;';
		$result = $adb->query($sql);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$output['OpenTickets'][$i] = $row;
		}
	}
	if (in_array('TicketsByStatus', $type) || in_array('all', $type)) {
		$sql = 'SELECT count(*) AS count, CASE WHEN vtiger_troubletickets.status IS NULL || vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0';
		if ($acc_id) {
			$sql .= " && vtiger_troubletickets.parent_id = $acc_id ";
		}
		$sql .= ' GROUP BY statusvalue;';
		$result = $adb->query($sql);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$row['statusvalue'] = Vtiger_Language_Handler::getTranslatedString($row['statusvalue'], 'HelpDesk', $session['lang']);
			$output['TicketsByStatus'][$i] = $row;
		}
	}
	if (in_array('TicketsSumTime', $type) || in_array('all', $type)) {
		$sql = 'SELECT SUM(sum_time) AS count, case when ( concat(vtiger_users.first_name, " " ,vtiger_users.last_name)  not like "") then
			concat(vtiger_users.first_name, " " ,vtiger_users.last_name) else vtiger_groups.groupname end as name, vtiger_users.id as id 
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0';
		if ($acc_id) {
			$sql .= " && vtiger_troubletickets.parent_id = $acc_id ";
		}
		$sql .= ' GROUP BY smownerid;';
		$result = $adb->query($sql);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$output['TicketsSumTime'][$i] = $row;
		}
	}
	if (in_array('ProjectSumTime', $type) || in_array('all', $type)) {
		$sql = sprintf("SELECT vtiger_project.sum_time AS count, vtiger_project.projectname
					FROM vtiger_project
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
					WHERE vtiger_crmentity.deleted = 0 && vtiger_project.linktoaccountscontacts IN (%s)", generateQuestionMarks($allowed_contacts_and_accounts));
		$result = $adb->pquery($sql, array($allowed_contacts_and_accounts));
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$output['ProjectSumTime'][$i] = $row;
		}
	}
	$log->debug("Entering customer portal function get_summary_widgets");
	return array($output);
}

function getPortalUserid()
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug("Entering customer portal function getPortalUserid");

	// Look the value from cache first
	$userid = Vtiger_Soap_YetiPortal::lookupPrefValue('userid');
	if ($userid === false) {
		$res = $adb->pquery("SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey = 'userid' && tabid = 0", []);
		$norows = $adb->num_rows($res);
		if ($norows > 0) {
			$userid = $adb->query_result($res, 0, 'prefvalue');
			// Update the cache information now.
			Vtiger_Soap_YetiPortal::updatePrefValue('userid', $userid);
		}
	}
	return $userid;
	$log->debug("Exiting customerportal function getPortalUserid");
}

function checkModuleActive($module)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();

	$isactive = false;
	$modules = get_modules(true);

	foreach ($modules as $key => $value) {
		if (strcmp($module, $value['name']) == 0) {
			$isactive = true;
			break;
		}
	}
	return $isactive;
}

/**
 *  Function that gives the Currency Symbol
 * @params $result $adb object - resultset
 * $column String column name
 * Return $value - Currency Symbol
 */
function getCurrencySymbol($result, $i, $column)
{
	$adb = PearDatabase::getInstance();
	$currencyid = $adb->query_result($result, $i, $column);
	$curr = \vtlib\Functions::getCurrencySymbolandRate($currencyid);
	$value = "(" . $curr['symbol'] . ")";
	return $value;
}

function getDefaultAssigneeId()
{
	$adb = PearDatabase::getInstance();
	$adb->println("Entering customer portal function getPortalUserid");

	// Look the value from cache first
	$defaultassignee = Vtiger_Soap_YetiPortal::lookupPrefValue('defaultassignee');
	if ($defaultassignee === false) {
		$res = $adb->pquery("SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey = 'defaultassignee' && tabid = 0", []);
		$norows = $adb->num_rows($res);
		if ($norows > 0) {
			$defaultassignee = $adb->query_result($res, 0, 'prefvalue');
			// Update the cache information now.
			Vtiger_Soap_YetiPortal::updatePrefValue('defaultassignee', $defaultassignee);
		}
	}
	return $defaultassignee;
}
/* Begin the HTTP listener service and exit. */
if (!isset($HTTP_RAW_POST_DATA)) {
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');
}
$server->service($HTTP_RAW_POST_DATA);
exit();
