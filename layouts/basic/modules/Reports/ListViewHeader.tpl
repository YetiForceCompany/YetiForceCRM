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
			<div class="listViewActionsDiv row">
				<div class="btn-toolbar col-md-4">
					<span class="btn-group listViewMassActions mr-1">
						<button class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown"><strong>{\App\Language::translate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
						<ul class="dropdown-menu">
							{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS}
								<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}"><a class="dropdown-item" href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{\App\Language::translate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a></li>
								{/foreach}
								{if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
								<li class="dropdown-divider"></li>
									{foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
									<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}"><a class="dropdown-item" {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'{else} href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}' {/if}>{\App\Language::translate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a></li>
									{/foreach}
								{/if}
						</ul>
					</span>
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{if $LISTVIEW_BASICACTION->getLabel() eq 'LBL_ADD_RECORD'}
							{assign var="childLinks" value=$LISTVIEW_BASICACTION->getChildLinks()}
							<span class="btn-group mr-1">
								<button class="btn btn-outline-secondary dropdown-toggle addButton" data-toggle="dropdown" id="{$MODULE}_listView_basicAction_Add">
									<span class="fas fa-plus"></span>&nbsp;
									<strong>{\App\Language::translate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong>&nbsp;
									<span class="caret icon-white"></span></button>
								<ul class="dropdown-menu">
									{foreach item="childLink" from=$childLinks}
										<li id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($childLink->getLabel())}">
											<a class="dropdown-item" href="javascript:void(0);" onclick='{$childLink->getUrl()|substr:strlen("javascript:")};'>{\App\Language::translate($childLink->getLabel(), $MODULE)}</a>
										</li>
									{/foreach}
								</ul>
							</span>
						{else}
							<span class="btn-group">
								<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn addButton btn-outline-secondary" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"'{/if}><span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong></button>
							</span>
						{/if}
					{/foreach}
				</div>
				<div class="foldersContainer btn-toolbar col-md-4">{include file=\App\Layout::getTemplatePath('ListViewFolders.tpl', $MODULE)}</div>
				<div class="col-md-4 btn-toolbar d-flex flex-row-reverse">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $MODULE)}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
		{/strip}
