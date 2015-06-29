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

<div class="bodyContents" style='min-width:1200px'>
	<div class="mainContainer">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="col-md-2 {if $LEFTPANELHIDE eq '1'} hide {/if}" id="leftPanel" style="min-height:550px;">
			{include file="ListViewSidebar.tpl"|vtemplate_path:$MODULE}
		</div>

		<div class="contentsDiv {if $LEFTPANELHIDE neq '1'} col-md-10 {/if}marginLeftZero" id="centerPanel" style="min-height:550px;">
			<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<span id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}glyphicon glyphicon-chevron-left{else}glyphicon glyphicon-chevron-right{/if}"></span>
			</div>&nbsp
			{include file="FindDuplicateHeader.tpl"|vtemplate_path:$MODULE}

{/strip}
