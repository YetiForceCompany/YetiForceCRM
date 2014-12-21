<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
	
//Uncomment the following lines to get the error message.
/*
$err = $GLOBALS["sclient"]->getError();
echo '<h2>Error Message</h2><pre>' . $err . '</pre>';
echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->request, ENT_QUOTES) . '</pre>';
echo '<h2>response</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
echo '<h2>debug</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->debug_str, ENT_QUOTES) . '</pre>';
exit;
*/

class Router
{
 	/*****************************************************************************
 	* Function: Router::start()
 	* Description: This function intercept the $_REQUEST parameters and call the requested action 
	* ****************************************************************************/
	public static function start() 
    {
    	global $avmod,$modulesNames,$targetmodule;
		$targetmoduleacces=true;
		$targetmodule=$_REQUEST['module'];
		$targetaction=$_REQUEST['action'];
		
		//Get a list of available module for this user 
		$params = array($_SESSION["loggeduser"]['id']);
		$avmod=$GLOBALS["sclient"]->call('get_modules',$params);
		//echo '<h2>response</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		//if no target module specified set HelpDesk as the default index module	
		if(!isset($targetmodule) || $targetmodule=="") $targetmodule="HelpDesk";
		
		//if a download for a file is requested call the function to retrieve the desired file
		if(isset($_REQUEST['downloadfile']) && $_REQUEST['downloadfile']==="true") User::download_file();

		//Check if the requested module is Available
		foreach($avmod as $key => $value){
			if($targetmodule == $value['name']){
				$targetmoduleacces=false;
			}
			$modulesNames[ $value['name'] ] = $value['translated_name'];
		}
		if($targetmoduleacces && ( $targetmodule != 'Home' ) ){
			$targetmodule="HelpDesk";//die("Not Autorized!");
		}
		//Require the base module class
		require_once("modules/Module/index.php");
		
		//if isset a specific file and a specific class for the requested module include the extended class and create the object
		if(file_exists("modules/".$targetmodule."/index.php")) require_once("modules/".$targetmodule."/index.php");
		if(class_exists($targetmodule)) $mod=new $targetmodule();
		else $mod=new BaseModule($targetmodule);
		
		//if is pesent a target id show the corrispondent entity else show a list of entities for the target module	
		if(isset($_REQUEST['id'])) $mod->detail($_REQUEST['id']);
		else if(isset($_REQUEST['productid'])) $mod->detail($_REQUEST['productid']);
		else if(isset($_REQUEST['faqid'])) $mod->detail($_REQUEST['faqid']);
		else if($targetmodule=="HelpDesk" && $targetaction=="new") $mod->create_new();
		else $mod->get_list();
		
		//if there is a request for change password call the modal again and show the request resault
		if(isset($GLOBALS["opresult"]) && $GLOBALS["opresult"]!="") echo "<script> $(function(){ $('#changePassModal').modal('show'); })</script>";
    }
}

class Template 
{
	/*****************************************************************************
 	* Function: Template::display()
 	* Description: This function receive 3 parameters the module wich you want to display, the data array to pass to the view
 	* and the view name (the name of the view file). 
	* ****************************************************************************/
    public static function display($module,$data,$viewname) 
    {
      //If a parameter theme is specified in the REQUEST, the theme will be setted as requestes, else the default theme will be loaded
      if(isset($_REQUEST['theme']) && $_REQUEST['theme']!="" && is_dir("themes/".$_REQUEST['theme']))
      $_SESSION["portal_theme"]=$_REQUEST['theme'];
      
      if(isset($_SESSION["portal_theme"])) $currtheme=$_SESSION['portal_theme'];
      
      else $currtheme=$GLOBALS["portal_theme"];
      
      //Require common header and menu files	
   	  require_once("themes/".$currtheme."/header.php");
	  require_once("themes/".$currtheme."/menu.php");

	  //Fallback cascade theme inclusion, first check if is present a specified view for the current module in the current theme folder
      if(file_exists("themes/".$currtheme."/modules/".$module."/".$viewname.".php")) 
		require_once("themes/".$currtheme."/modules/".$module."/".$viewname.".php");
	
	  //else check if is present a specified DEFAULT view for the current module in the current module folder
	  else if(file_exists("modules/".$module."/layouts/".$viewname.".php")) 
		require_once("modules/".$module."/layouts/".$viewname.".php");
	  
	  //else check if is present a specified DEFAULT view for COMMON modules in the current theme module folder 	
	  else if(file_exists("themes/".$currtheme."/modules/Module/".$viewname.".php")) 
		require_once("themes/".$currtheme."/modules/Module/".$viewname.".php");
	
	  //else require the DEFAULT view for COMMON modules in the comon module folder
	  else require_once("modules/Module/layouts/".$viewname.".php");
	  
	  //Require common footer file
	  require_once("themes/".$currtheme."/footer.php");
	  
    }
}

class Language
{
	/*****************************************************************************
	* Function: Language::translate()
	* Description: This function return the translated string, if it is found in the language file, else it will return the term as
	* prompted
	* ****************************************************************************/
	public static function translate($term,$lang=false) 
	{
		global $default_language, $languages;
		$lang = array_key_exists($_SESSION["loggeduser"]['language'],$languages)?$_SESSION["loggeduser"]['language']:$default_language;
		if(file_exists("language/".$lang.".lang.php")) include("language/".$lang.".lang.php");
		if(isset($app_strings[$term])) return $app_strings[$term];
		else return $term;
	}
}

class User
{
	/*****************************************************************************
	* Function: User::check_login()
	* ****************************************************************************/
	public static function check_login() 
	{
		global $opresult;
		//ADDED TO ENABLE THEME SWITCHING
		if(isset($_REQUEST['theme']) && $_REQUEST['theme']!="" && is_dir("themes/".$_REQUEST['theme']))
			$_SESSION["portal_theme"]=$_REQUEST['theme'];
		if(isset($_SESSION["portal_theme"])) $currtheme=$_SESSION['portal_theme'];
		else $currtheme=$GLOBALS["portal_theme"];

		if(isset($_REQUEST['logout'])) {
			session_unset();
			$_SESSION["portal_theme"]=$currtheme;
			header("Location: index.php");
		}
		if(!isset($_SESSION['loggeduser']) || $_SESSION["loggeduser"]=="ERROR") {
			$login=false;
			if(isset($_REQUEST["email"]) && isset($_REQUEST["pass"])) $login=User::portal_login($_REQUEST["email"],$_REQUEST["pass"]);
			if(isset($_REQUEST["email"]) && isset($_REQUEST["forgot"])) $lres=User::forgot_password($_REQUEST["email"]);

			if(!$login || $login[0]=="LBL_INVALID_USERNAME_OR_PASSWORD") {
				if($login[0]=="LBL_INVALID_USERNAME_OR_PASSWORD") $loginerror= $login[0];
				
				if(isset($lres) && $lres=="ERROR") $loginerror="The Email you Request is not in our system!";
				else if(isset($lres) && $lres=="SUCCESS") $successmess="We have send an Email containing your Password at the requested Address!";
			
				if(file_exists("themes/".$currtheme."/login.php")) require_once("themes/".$currtheme."/login.php");
				else require_once("themes/default/login.php"); 
				
				session_unset();
				die();
			}
		}
		else User::portal_login($_SESSION['loggeduser']['user_name'],$_SESSION['loggeduser']['user_password']);
		if(isset($_SESSION['loggeduser']) && isset($_REQUEST['fun']) && $_REQUEST['fun']=="changepassword")
		$GLOBALS["opresult"]=User::change_password();
	}

	/*****************************************************************************
	* Function: User::forgot_password()
	* ****************************************************************************/

	function forgot_password($email){
	
		$params = array('email' => $email);
		$result = $GLOBALS["sclient"]->call('send_mail_for_password', $params);
	
		if(strpos($result,'false')!==false) return "ERROR";
		else return "SUCCESS";
		
	}


	/*****************************************************************************
	* Function: User::portal_login()
	* ****************************************************************************/

	function portal_login($email,$password){
		if(isset($_REQUEST['lang']) && file_exists("language/".$_REQUEST['lang'].".lang.php")) $portalLang = $_REQUEST['lang'];
		else if(isset($_SESSION["loggeduser"]['language'])) $portalLang=$_SESSION["loggeduser"]['language'];
		else $portalLang = $default_language;
		$params = array(
			'user_name' => "$email",
			'user_password'=>"$password",
			'version' => "6.0.1",
			'portal_lang'=>"$portalLang"
		);
		$result = $GLOBALS["sclient"]->call('authenticate_user', $params);
		//echo '<h2>request</h2><pre>' . htmlspecialchars($GLOBALS["sclient"]->response, ENT_QUOTES) . '</pre>';
		if(is_array($result[0]) && isset($result[0]['id'])){ 
			$_SESSION["loggeduser"] = $result[0];
			$_SESSION["loggeduser"]['language'] = $portalLang;
		}
		return $result;
	}
	
	
	/*****************************************************************************
	* Function: User::change_password()
	* Parameters: $_REQUEST Array
	* Description: This function is derived from the original change_password function 
	* written in the Vtiger Customer Portal by the Vtiger Team
	* ****************************************************************************/
		
	//Added for My Settings - Save Password
	function change_password($version="6.0.0")
	{
		$customer_id = $_SESSION["loggeduser"]['id'];
		$customer_name = $_SESSION["loggeduser"]['user_name'];
		$oldpw = trim($_REQUEST['old_password']);
		$newpw = trim($_REQUEST['new_password']);
		$confirmpw = trim($_REQUEST['confirm_password']);
		
		$params = array(
			'user_name' => "$customer_name",
			'user_password'=>"$oldpw",
			'version' => "$version",
			'portal_lang'=>$_SESSION["loggeduser"]['language'],
			'login'=>'false'
		);
		$result = $GLOBALS["sclient"]->call('authenticate_user',$params);
		$sessionid = $_SESSION["loggeduser"]['sessionid'];

		if(isset($result) && isset($result[0]['user_password']) && $oldpw == $result[0]['user_password'])
		{
			if(strcasecmp($newpw,$confirmpw) == 0)
			{
				$customerid = $result[0]['id'];
				$sessionid = $_SESSION["loggeduser"]['sessionid'];
				$params = array(array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'username'=>"$customer_name",'old_password'=>"$oldpw",'new_password'=>"$newpw",'version'=>"$version"));
				$result = $GLOBALS["sclient"]->call('change_password',$params);
				$_SESSION["loggeduser"]['user_password'] = $newpw;
				$errormsg .= $result[0];
			}
			else
			{
				$errormsg .= 'LBL_ENTER_NEW_PASSWORDS_SAME';
			}
		}
		else
		{
			$errormsg .= $result[0];
		}
		return $errormsg;
	}


	/*****************************************************************************
	* Function: User::download_file()
	* Parameters: $_REQUEST Array
	* Description: This function is derived from the original download function 
	* written in the Vtiger Customer Portal by the Vtiger Team
	* ****************************************************************************/

	function download_file(){
		$filename = $_REQUEST['filename'];
		$fileType = $_REQUEST['filetype'];
		//$fileid = $_REQUEST['fileid'];
		$filesize = $_REQUEST['filesize'];

		//Added for enhancement from Rosa Weber

		if($_REQUEST['module'] == 'Invoice' || $_REQUEST['module'] == 'Quotes')
		{
			$id=$_REQUEST['id'];
			$block = $_REQUEST['module'];
			$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>$_SESSION["loggeduser"]['id'],'sessionid'=>$_SESSION["loggeduser"]['sessionid']);
			$fileContent = $GLOBALS["sclient"]->call('get_pdf', $params);
			$fileType ='application/pdf';
			$fileContent = $fileContent[0];
			$filesize = strlen(base64_decode($fileContent));
			$filename = "$block.pdf";

		}
		else if($_REQUEST['module'] == 'Documents')
		{
			$id=$_REQUEST['id'];
			$folderid = $_REQUEST['folderid'];
			$block = $_REQUEST['module'];
			$params = array('id' => "$id", 'folderid'=> "$folderid",'block'=>"$block", 'contactid'=>$_SESSION["loggeduser"]['id'],'sessionid'=>$_SESSION["loggeduser"]['sessionid']);
			$result = $GLOBALS["sclient"]->call('get_filecontent_detail', $params);
			$fileType=$result[0]['filetype'];
			$filesize=$result[0]['filesize'];
			$filename=html_entity_decode($result[0]['filename']);
			$fileContent=$result[0]['filecontents'];
		}
		else
		{
			$ticketid = $_REQUEST['id'];
			$fileid = $_REQUEST['fileid'];
			//we have to get the content by passing the customerid, fileid and filename
			$customerid = $_SESSION["loggeduser"]['id'];
			$sessionid = $_SESSION["loggeduser"]['sessionid'];
			$params = array(array('id'=>$customerid,'fileid'=>$fileid,'filename'=>$filename,'sessionid'=>$sessionid,'ticketid'=>$ticketid));
			$fileContent = $GLOBALS["sclient"]->call('get_filecontent', $params);
			$fileContent = $fileContent[0];
			$filesize = strlen(base64_decode($fileContent));

		}
		// : End

		//we have to get the content by passing the customerid, fileid and filename
		$customerid = $_SESSION["loggeduser"]['id'];
		$sessionid = $_SESSION["loggeduser"]['sessionid'];

		header("Content-type: $fileType");
		header("Content-length: $filesize");
		header("Cache-Control: private");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Description: PHP Generated Data");
		echo base64_decode($fileContent);
		exit;
	}
}
class Functions
{
 	/*****************************************************************************
 	* Function: Functions::loadDataTable()
 	* Description: This function intercept the $_REQUEST parameters and call the requested action 
	* ****************************************************************************/
	public static function loadDataTable() 
    {
		echo '<script>
			$(document).ready(function() {
				$(".dataTablesContainer").dataTable( {
					"language": {
						"lengthMenu": "'.Language::translate("LBL_RECORDS_PER_PAGE").'",
						"zeroRecords": "'.Language::translate("LBL_NOTHING_FOUND").'",
						"info": "'.Language::translate("LBL_SHOWING_PAGE").'",
						"infoEmpty": "",
						"infoPostFix": "",
						"url": "",
						"infoFiltered": "'.Language::translate("LBL_FILTERED_TOTAL_RECORDS").'",
						"processing": "'.Language::translate("LBL_PROCESSING").'",
						"search": "'.Language::translate("LBL_SEARCH").' ",
						"paginate": {
							"first": "'.Language::translate("LBL_FIRST").'",
							"previous": "'.Language::translate("LBL_PREVIOUS").'",
							"next": "'.Language::translate("LBL_NEXT").'",
							"last": "'.Language::translate("LBL_LAST").'"
						}
					}
				} );
			});
			</script>';
    }
}