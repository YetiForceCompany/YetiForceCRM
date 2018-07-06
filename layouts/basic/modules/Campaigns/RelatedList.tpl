{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
    {if !empty($CUSTOM_VIEWS)}
        <div class="relatedContainer listViewPageDiv m-0">
            <input type="hidden" name="emailEnabledModules" value=true />
            <input type="hidden" id="view" value="{$VIEW}" />
            <input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
            <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
            <input type="hidden" value="{$ORDER_BY}" id="orderBy" />
            <input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
            <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries" />
            <input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit' />
            <input type="hidden" id="recordsCount" value="" />
            <input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids={\App\Json::encode($SELECTED_IDS)} />
            <input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids={\App\Json::encode($EXCLUDED_IDS)} />
            <input type="hidden" id="recordsCount" name="recordsCount" />
            <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount' />
			<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
            <div class="relatedHeader">
                <div class="btn-toolbar row">
					<div class="col-md-9">
						<div class="btn-group listViewMassActions btn-group pr-2">
							{if isset($RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS'])}
								<button class="btn btn-light dropdown-toggle" data-toggle="dropdown"><strong>{\App\Language::translate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
								<ul class="dropdown-menu">
								{foreach item="LISTVIEW_MASSACTION" from=$RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS'] name=actionCount}
									<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}">
										<a class="dropdown-item" href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{\App\Language::translate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a>
									</li>
									{if $smarty.foreach.actionCount.last eq true}
										<li class="dropdown-divider"></li>
									{/if}
								{/foreach}
								{if isset($RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS_ADV'])}
									{foreach item=LISTVIEW_ADVANCEDACTIONS from=$RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS_ADV']}
										<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}">
											<a
											{if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0}
												href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'
											{else}
												href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}'
											{/if}
											class="dropdown-item{if $LISTVIEW_ADVANCEDACTIONS->get('linkclass') neq ''} {$LISTVIEW_ADVANCEDACTIONS->get('linkclass')}{/if}"
											{if isset($LISTVIEW_ADVANCEDACTIONS->get('linkdata'))}
												{foreach from=$LISTVIEW_ADVANCEDACTIONS->get('linkdata') key=NAME item=DATA}
													data-{$NAME}="{$DATA}"
												{/foreach}
											{/if}
											>{\App\Language::translate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a>
										</li>
									{/foreach}
								{/if}
								</ul>
							{/if}
						</div>
						<div class="btn-group col-md-3">
							<span class="customFilterMainSpan">
								{if isset($CUSTOM_VIEWS)}
									<select id="recordsFilter" class="col-md-12" data-placeholder="{\App\Language::translate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE_NAME)}">
										{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
											<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
												{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
													<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" value="{$CUSTOM_VIEW->get('cvid')}" class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {\App\Language::translate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}{else}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									<span class="filterImage">
										<span class="fas fa-filter"></span>
									</span>
								{else}
									<input type="hidden" value="0" id="customFilter" />
								{/if}
							</span>
						</div>
						<div class="btn-group pr-2">
							<button type="button" class="btn btn-light loadFormFilterButton js-popover-tooltip"	data-js="popover" data-content="{\App\Language::translate('LBL_LOAD_RECORDS_INFO',$MODULE)}">
								<span class="fas fa-filter"></span>&nbsp;
								<strong>{\App\Language::translate('LBL_LOAD_RECORDS',$MODULE)}</strong>
							</button>
						</div>
						{if isset($RELATED_LIST_LINKS['LISTVIEWBASIC'])}
							{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
								<div class="btn-group pr-2">
									{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
									{assign var=IS_SEND_EMAIL_BUTTON value={$RELATED_LINK->get('_sendEmail')}}
									<button type="button" class="btn btn-light addButton
											{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
											{if $IS_SELECT_BUTTON eq true} data-moduleName='{$RELATED_LINK->get('_module')->get('name')}'{/if}
											{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
											{if $IS_SEND_EMAIL_BUTTON eq true}	onclick="{$RELATED_LINK->getUrl()}" {else} data-url="{$RELATED_LINK->getUrl()}"{/if}
											{if ($IS_SELECT_BUTTON eq false) and ($IS_SEND_EMAIL_BUTTON eq false)}
												name="addButton">
											{else}
												> {* closing the button tag *}
											{/if}
											{if $RELATED_LINK->get('linkicon') neq ''}
												<span class="{$RELATED_LINK->get('linkicon')}"></span>
											{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong>
									</button>
								</div>
							{/foreach}
						{/if}
					</div>
					<div class="col-md-3">
						<div class="float-right">
							{if $VIEW_MODEL}
								<div class="float-right pl-1">
									{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
									<input type="hidden" class="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}" />
									<div class="dropdown dropdownEntityState u-remove-dropdown-icon">
										<button class="btn btn-light dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
											{if $VIEW_MODEL->get('entityState') === 'Archived'}
												<span class="fas fa-archive"></span>
											{elseif $VIEW_MODEL->get('entityState') === 'Trash'}
												<span class="fas fa-trash-alt"></span>
											{elseif $VIEW_MODEL->get('entityState') === 'All'}
												<span class="fas fa-bars"></span>
											{else}
												<span class="fas fa-undo-alt"></span>
											{/if}
										</button>
										<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
											<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
												<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') === 'Active'} active{/if}" href="#" data-value="Active">
													<span class="fas fa-undo-alt mr-1"></span>&nbsp;
													{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}
												</a>
											</li>
											<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
												<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Archived'} active{/if}" href="#" data-value="Archived">
													<span class="fas fa-archive mr-1"></span>
													{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
												</a>
											</li>
											<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
												<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Trash'} active{/if}" href="#" data-value="Trash">
													<span class="fas fa-trash-alt mr-1"></span>
													{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
												</a>
											</li>
											<li>
												<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'All'} active{/if}" href="#" data-value="All">
													<span class="fas fa-bars mr-1"></span>
													{\App\Language::translate('LBL_ALL')}
												</a>
											</li>
										</ul>
									</div>
								</div>
							{/if}
						</div>
						<div class="paginationDiv float-right">
							{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE) VIEWNAME='related'}
						</div>
					</div>
				</div>
			</div>
			<div id="selectAllMsgDiv" class="alert-block msgDiv">
				<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($RELATED_MODULE->get('name'))}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
			</div>
			<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
				<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
			</div>
			<div class="contents-topscroll">
				<div class="topscroll-div">
					&nbsp;
				</div>
			</div>
			<div class="relatedContents">
				<div>
					{include file=\App\Layout::getTemplatePath("RelatedListContents.tpl", $RELATED_MODULE->get('name'))}
				</div>
			</div>
		</div>
	{else}
		{include file=\App\Layout::getTemplatePath('RelatedList.tpl')}
	{/if}
{/strip}
