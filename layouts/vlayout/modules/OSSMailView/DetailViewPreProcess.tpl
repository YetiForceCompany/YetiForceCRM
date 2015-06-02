{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
{include file="Header.tpl"|vtemplate_path:$MODULE_NAME}
{include file="BasicHeader.tpl"|vtemplate_path:$MODULE_NAME}

<div class="bodyContents">
	<div class="mainContainer row-fluid">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="span2{if $LEFTPANELHIDE eq '1'} hide {/if} row-fluid" id="leftPanel">
			{include file="DetailViewSidebar.tpl"|vtemplate_path:$MODULE_NAME}
		</div>
		<div class="contentsDiv {if $LEFTPANELHIDE neq '1'} span10 {else} span12 {/if}marginLeftZero" id="centerPanel">
			<div id="toggleButton" class="toggleButton" title="Left Panel Show/Hide"> 
				<span id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></span>
			</div>
				{include file="DetailViewHeader.tpl"|vtemplate_path:'OSSMailView'}

{/strip}