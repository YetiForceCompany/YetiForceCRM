{*<!--
/************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
{strip}
	<div class="widget_header row">
		<div class="col-xs-9 col-sm-4 col-md-6">
			<div class="btn-group listViewMassActions modOn_{$MODULE} pull-left paddingRight10">
				{include file='ButtonViewLinks.tpl'|@vtemplate_path LINKS=$QUICK_LINKS['SIDEBARLINK'] BTN_GROUP=false}
			</div>
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
{/strip}
