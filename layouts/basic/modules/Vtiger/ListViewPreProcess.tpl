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
	{include file="Header.tpl"|vtemplate_path:$MODULE}
	<div class="bodyContents">
		<div class="mainContainer">
			<div class="contentsDiv">
				<div class="widget_header row marginBottom10px">
					<div class="col-sm-6 col-xs-12">
						{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					</div>
					<div class="col-sm-6 col-xs-12">
						<div class="pull-right">
							{foreach item=LINK from=$HEADER_LINKS['LIST_VIEW_HEADER']}
								{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='listViewHeader'}
							{/foreach}
						</div>
					</div>
				</div>
				{include file="ListViewHeader.tpl"|vtemplate_path:$MODULE}
			{/strip}
