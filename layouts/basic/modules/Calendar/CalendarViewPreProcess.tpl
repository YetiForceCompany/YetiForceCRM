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
	{include file=\App\Layout::getTemplatePath('Header.tpl', $MODULE)}
	<div class="bodyContents">
		<div class="mainContainer">
			<div class="o-breadcrumb js-breadcrumb widget_header d-flex justify-content-between align-items-center px-2 flex-column flex-sm-row" data-js="height">
				<div class="mr-auto">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
				<div class="btn-group btn-toolbar mb-1 mb-sm-0 ml-sm-1">
				{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$QUICK_LINKS['SIDEBARLINK'] CLASS='listViewMassActions btn-group' BTN_CLASS='btn-outline-dark'}
				<button class="btn btn-outline-dark addButton">
						<span class="fas fa-plus"></span>
					</button>
				</div>
			</div>
			<div id="centerPanel" class="contentsDiv">
			{/strip}
