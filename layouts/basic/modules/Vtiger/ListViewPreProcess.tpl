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
			<div class="contentsDiv">
				<div class="widget_header row align-items-center mb-2">
					<div class="col-sm-6 col-12">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
					</div>
					<div class="col-sm-6 col-12">
						<div class="float-right">
							{foreach item=LINK from=$HEADER_LINKS['LIST_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewHeader'}
							{/foreach}
						</div>
					</div>
				</div>
				{include file=\App\Layout::getTemplatePath('ListViewHeader.tpl', $MODULE)}
			{/strip}
