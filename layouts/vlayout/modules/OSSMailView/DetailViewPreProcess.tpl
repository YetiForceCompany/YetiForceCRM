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
{include file="Header.tpl"|vtemplate_path:$MODULE}
<div class="bodyContents">
	<div class="mainContainer">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="col-md-2{if $LEFTPANELHIDE eq '1'} hide {/if}" id="leftPanel">
			{include file="DetailViewSidebar.tpl"|vtemplate_path:$MODULE_NAME}
		</div>
		<div class="contentsDiv {if $LEFTPANELHIDE neq '1'} col-md-10 {else} col-md-12 {/if}marginLeftZero" id="centerPanel">
			<div id="toggleButton" class="toggleButton" title="Left Panel Show/Hide"> 
				<span id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}glyphicon glyphicon-chevron-left{else}glyphicon glyphicon-chevron-right{/if}"></span>
			</div>
				{include file="DetailViewHeader.tpl"|vtemplate_path:'OSSMailView'}
{/strip}
