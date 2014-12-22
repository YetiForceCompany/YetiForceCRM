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
<div class="listViewPageDiv">
	<div class="listViewTopMenuDiv">
		<div class="listViewActionsDiv row-fluid">
			<span class="btn-toolbar span4">
				<span class="btn-group listViewMassActions">
					<button class="btn dropdown-toggle" data-toggle="dropdown"><strong>{vtranslate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<i class="caret"></i></button>
					<ul class="dropdown-menu">
						{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS}
							<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}"><a href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{vtranslate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a></li>
						{/foreach}
						{if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
							<li class="divider"></li>
							{foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
								<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}"><a {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'{else} href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}' {/if}>{vtranslate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a></li>
							{/foreach}
						{/if}
					</ul>
				</span>
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
					{if $LISTVIEW_BASICACTION->getLabel() eq 'LBL_ADD_RECORD'}
						{assign var="childLinks" value=$LISTVIEW_BASICACTION->getChildLinks()}
						<span class="btn-group">
							<button class="btn dropdown-toggle addButton" data-toggle="dropdown" id="{$MODULE}_listView_basicAction_Add">
								<i class="icon-plus"></i>&nbsp;
								<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong>&nbsp;
								<i class="caret icon-white"></i></button>
							<ul class="dropdown-menu">
								{foreach item="childLink" from=$childLinks}
									<li id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($childLink->getLabel())}">
										<a href="javascript:void(0);" onclick='{$childLink->getUrl()|substr:strlen("javascript:")};'>{vtranslate($childLink->getLabel(), $MODULE)}</a>
									</li>
								{/foreach}
							</ul>
						</span>
					{else}
						<span class="btn-group">
							<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn addButton" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}><i class="icon-plus"></i>&nbsp;<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button>
						</span>
					{/if}
				{/foreach}
			</span>
			<span class="foldersContainer btn-toolbar span4">{include file='ListViewFolders.tpl'|@vtemplate_path:$MODULE}</span>
			<span class="span4 btn-toolbar">
				{include file='ListViewActions.tpl'|@vtemplate_path:$MODULE}
			</span>
		</div>
	</div>
<div class="listViewContentDiv" id="listViewContents">
{/strip}