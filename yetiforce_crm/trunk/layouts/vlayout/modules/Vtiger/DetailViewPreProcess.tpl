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
{include file="Header.tpl"|vtemplate_path:$MODULE_NAME}
{include file="BasicHeader.tpl"|vtemplate_path:$MODULE_NAME}

<div class="bodyContents">
	<div class="mainContainer row-fluid">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="span2{if $LEFTPANELHIDE eq '1'} hide {/if} row-fluid" id="leftPanel" style="min-height:550px;">
			{include file="DetailViewSidebar.tpl"|vtemplate_path:$MODULE_NAME}
		</div>
		<div class="contentsDiv {if $LEFTPANELHIDE neq '1'} span10 {/if}marginLeftZero" id="rightPanel" style="min-height:550px;">
			<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
			</div>
				{include file="DetailViewHeader.tpl"|vtemplate_path:$MODULE_NAME}

{/strip}