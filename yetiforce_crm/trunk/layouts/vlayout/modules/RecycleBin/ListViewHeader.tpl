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
					{if $LISTVIEW_MASSACTIONS}
					<span class="btn-group listViewMassActions">
						<button class="btn dropdown-toggle" data-toggle="dropdown"><strong>{vtranslate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<i class="caret"></i></button>
						<ul class="dropdown-menu">
							{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS}
								<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}">
									<a href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{vtranslate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a>
								</li>
							{/foreach}
						</ul>
					</span>
					{/if}
					{* Fix for empty Recycle bin Button *} 
                                        {foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']} 
                                                <span class="btn-group">  
                                                    <button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn clearRecycleBin" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if} {if $DELETED_RECORDS_TOTAL_COUNT eq 0} disabled="disabled" {/if}>&nbsp;<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button> 
                                                </span> 
                                        {/foreach} 
				</span>
			<span class="btn-toolbar span4">
				<span class="customFilterMainSpan btn-group">
					{if $MODULE_LIST|@count gt 0}
						<select id="customFilter" style="width:350px;">
							{foreach item=MODULEMODEL from=$MODULE_LIST}
								{if $SOURCE_MODULE eq $MODULEMODEL->get('name')}
									<option  value="{$MODULEMODEL->get('name')}" selected="">{vtranslate($MODULEMODEL->get('name'),$MODULEMODEL->get('name'))}</option>
								{else if $MODULEMODEL->get('name') neq 'Events'}
									<option  value="{$MODULEMODEL->get('name')}">{vtranslate($MODULEMODEL->get('name'),$MODULEMODEL->get('name'))}</option>
								{/if}
							{/foreach}
						</select>
					{/if}
				</span>
			</span>
			<span class="span4 btn-toolbar">
				{include file='ListViewActions.tpl'|@vtemplate_path}
			</span>
		</div>
		</div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}