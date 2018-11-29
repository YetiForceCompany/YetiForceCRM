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
<div class=" listViewPageDiv">
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<div class="listViewActionsDiv my-2 row">
		<div class="col-md-4 btn-toolbar">
				<span class="btn-group listViewMassActions">
					{if count($LISTVIEW_MASSACTIONS) gt 0 || $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
						<button class="btn btn-light mr-1 dropdown-toggle" data-toggle="dropdown">
							<span class="fas fa-list mr-1"></span>
							{\App\Language::translate('LBL_ACTIONS', $MODULE)}
						</button>
						<ul class="dropdown-menu">
							{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS name=actionCount}
								<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}">
									<a class="dropdown-item" href="javascript:void(0);"
									   {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else}
									   onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >
										{if $LISTVIEW_MASSACTION->get('linkicon') neq ''}
											<span class="{$LISTVIEW_MASSACTION->get('linkicon')}"></span>
											&nbsp;&nbsp;
										{/if}
										{\App\Language::translate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}
									</a>
								</li>

{if $smarty.foreach.actionCount.last eq true}
								<li class="dropdown-divider"></li>
							{/if}
							{/foreach}
							{foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
								<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}">
									<a class="dropdown-item" {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'{else} href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}' {/if}>
										{if $LISTVIEW_ADVANCEDACTIONS->get('linkicon') neq ''}
											<span class="{$LISTVIEW_ADVANCEDACTIONS->get('linkicon')}"></span>
											&nbsp;&nbsp;
										{/if}
										{\App\Language::translate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}
									</a>
								</li>
							{/foreach}
						</ul>
					{/if}
				</span>
			{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
				<span class="btn-group">
						<button class="btn btn-light addButton" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
								{else} onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
										<span class="fas fa-plus mr-1"></span>
							{\App\Language::translate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}
									</button>
								</span>
			{/foreach}
		</div>
		<div class="col-md-4 btn-toolbar ml-0">
			<div class="customFilterMainSpan ml-auto mx-xl-auto">
				{if $CUSTOM_VIEWS|@count gt 0}
					<select id="customFilter" class="form-control"
							title="{\App\Language::translate('LBL_CUSTOM_FILTER')}">
						{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
							<optgroup
									label='{\App\Language::translate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}'>
								{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
									<option data-editable="false"
											data-deletable="false"
											{if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'System'}
												data-option="{$CUSTOM_VIEW->getOwnerName()}"
											{/if}
											id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" {' '}
											value="{$CUSTOM_VIEW->get('cvid')}" {' '}
											data-id="{$CUSTOM_VIEW->get('cvid')}" {if $VIEWID neq '' && $VIEWID neq '0'  && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected" {elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected" {/if}
											class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">
										{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $MODULE)}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				{else}
					<input type="hidden" value="0" id="customFilter"/>
				{/if}
			</div>
		</div>
		<div class="col-md-4">
			<div class="float-right">
				{include file=\App\Layout::getTemplatePath('ListViewActions.tpl', $QUALIFIED_MODULE)}
			</div>
		</div>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
		{/strip}
