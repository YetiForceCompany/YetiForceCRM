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
<div class="">
	{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	<hr>
	<div class="widget_header row">
		<div class="col-md-2 pull-left">
			<select class="chzn-select form-control" id="usersFilter" >
				<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
				{foreach item=USERNAME key=USER from=$USERSLIST}
					<option value="{$USER}" name="{$USERNAME}" {if $USERNAME eq $SELECTED_USER} selected {/if}>{$USERNAME}</option>
				{/foreach}
			</select>
		</div>
		<div class="col-md-10 pull-right">
			{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}
