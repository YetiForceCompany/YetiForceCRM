{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<nav class="navbar navbar-fixed-top navbar-inverse noprint commonActionsContainer" role="navigation" >
		<div class="navbar-header">
			<button type="button" data-target="#topMenus" data-toggle="collapse" class="navbar-toggle">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			{*<a href="#" class="navbar-brand">Brand</a>*}
		</div>
		{include file='MenuBar.tpl'|@vtemplate_path}
		<div class="collapse navbar-collapse actionsContainer">
			{include file='CommonActions.tpl'|@vtemplate_path}
		</div>
	</nav>
{/strip}
