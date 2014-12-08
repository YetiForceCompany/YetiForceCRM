{*<!--
/********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}

{include file="Header.tpl"|vtemplate_path:$MODULE}
{include file="BasicHeader.tpl"|vtemplate_path:$MODULE}

{strip}
<div class="bodyContents">
	<div class="mainContainer row-fluid">
		<div class="span12">
			<iframe id="ui5frame" src="{$UI5_URL}" width="100%" height="650px" style="border:0;"></iframe>
		</div>
{/strip}