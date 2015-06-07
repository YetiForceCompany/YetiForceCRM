<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
require_once 'config/api.php';
$service = $_REQUEST['service'];

if(isset($service))
{
	if(!in_array($service,$enabledServices)){
		die($service.' - Service is not active');
	}	
	if($service == "customerportal")
	{
		include("api/customerportal.php");
	}	
	elseif($service == "mobile")	
	{		
		include("api/mobile.php");
	}
	elseif($service == "yetiportal")	
	{		
		include("api/yetiportal.php");
	}
	elseif($service == "firefox")
	{
		include("api/firefoxtoolbar.php");
	}
	elseif($service == "wordplugin")
	{
		include("api/wordplugin.php");
	}
	elseif($service == "thunderbird")
	{
		include("api/thunderbirdplugin.php");
	}
	else
	{
		echo "No Service Configured for ". strip_tags($service);
	}
}
else
{
	echo "<h1>YetiForceCRM API Services</h1>";
	echo "<li>YetiForceCRM Yeti Portal EndPoint URL -- Click <a href='api.php?service=yetiportal'>here</a></li>";
	echo "<li>YetiForceCRM Outlook Plugin EndPoint URL -- Click <a href='api.php?service=outlook'>here</a></li>";
	echo "<li>YetiForceCRM Word Plugin EndPoint URL -- Click <a href='api.php?service=wordplugin'>here</a></li>";
	echo "<li>YetiForceCRM ThunderBird Extenstion EndPoint URL -- Click <a href='api.php?service=thunderbird'>here</a></li>";
	echo "<li>YetiForceCRM Customer Portal EndPoint URL -- Click <a href='api.php?service=customerportal'>here</a></li>";
	echo "<li>YetiForceCRM WebForm EndPoint URL -- Click <a href='api.php?service=webforms'>here</a></li>";
	echo "<li>YetiForceCRM FireFox Extension EndPoint URL -- Click <a href='api.php?service=firefox'>here</a></li>";
}
