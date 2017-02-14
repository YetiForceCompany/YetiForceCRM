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
<div class="listViewPageDiv">
	<div class="listViewTopMenuDiv">
		<div class="widget_header row">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{if isset($SELECTED_PAGE)}
					{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 btn-toolbar">
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
				<button class="btn addButton btn-success" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
						{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
					<i class="glyphicon glyphicon-plus"></i>&nbsp;
					<strong>{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
				</button>
				{/foreach}
			</div>
			<div class="col-md-4 btn-toolbar marginLeftZero" >
				<select class="chzn-select form-control" id="moduleFilter" style="margin-left:5px;">
					<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
					{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
						<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}">
							{if $MODULE_MODEL->getName() eq 'Calendar'}
								{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
							{else}
								{vtranslate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							{/if}
						</option>
					{/foreach}
				</select>

			</div>
			<div class="col-md-4 ">
				{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
		</div>
		</div>
	</div>
	<div class="listViewContentDiv listViewPageDiv" id="listViewContents">
{/strip}
