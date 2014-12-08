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
{include file="BasicHeader.tpl"|vtemplate_path:$MODULE}
<div class="bodyContents">
	<div class="mainContainer row-fluid">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="span2 row-fluid {if $LEFTPANELHIDE eq '1'} hide {/if}" id="leftPanel" style="min-height:550px;">
			{include file="ListViewSidebar.tpl"|vtemplate_path:$MODULE_NAME}
		</div>
		<div class="contentsDiv {if $LEFTPANELHIDE neq '1'} span10 {/if}marginLeftZero" id="rightPanel" style="min-height:550px;">
			<div>
			<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
			</div>&nbsp
				{include file="dashboards/DashBoardHeader.tpl"|vtemplate_path:$MODULE_NAME DASHBOARDHEADER_TITLE=vtranslate($MODULE, $MODULE)}
{/strip}