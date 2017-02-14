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
        <div class="relatedContainer listViewPageDiv margin0px">
            <input type="hidden" name="emailEnabledModules" value=true />
            <input type="hidden" id="view" value="{$VIEW}" />
            <input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
            <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
            <input type="hidden" value="{$ORDER_BY}" id="orderBy">
            <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
            <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
            <input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
            <input type="hidden" id="recordsCount" value=""/>
            <input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids={\App\Json::encode($SELECTED_IDS)} />
            <input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids={\App\Json::encode($EXCLUDED_IDS)} />
            <input type="hidden" id="recordsCount" name="recordsCount" />
            <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
			<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
            <div class="relatedHeader">
                <div class="btn-toolbar row">
                    <div class="col-md-4">
                        {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                            <div class="btn-group paddingRight10">
                                {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                                {assign var=IS_SEND_EMAIL_BUTTON value={$RELATED_LINK->get('_sendEmail')}}
                                <button type="button" class="btn btn-default addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} moduleColor_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
										{if $IS_SELECT_BUTTON eq true} data-moduleName='{$RELATED_LINK->get('_module')->get('name')}' {/if}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
										{if $IS_SEND_EMAIL_BUTTON eq true}	onclick="{$RELATED_LINK->getUrl()}" {else} data-url="{$RELATED_LINK->getUrl()}"{/if}
										{if ($IS_SELECT_BUTTON eq false) and ($IS_SEND_EMAIL_BUTTON eq false)}
											name="addButton"><span class="glyphicon glyphicon-plus"></span>
										{else}
											> {* closing the button tag *}
										{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong>
								</button>
							</div>
						{/foreach}
						&nbsp;
					</div>
					<div class="col-md-2">
						<span class="customFilterMainSpan">
							{if $CUSTOM_VIEWS|@count gt 0}
								<select id="recordsFilter" class="col-md-12" data-placeholder="{vtranslate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE_NAME)}">
									{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
										<optgroup label="{vtranslate($GROUP_LABEL)}">
											{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
												<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" value="{$CUSTOM_VIEW->get('cvid')}" class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {vtranslate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}{else}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
								<span class="filterImage">
									<span class="glyphicon glyphicon-filter"></span>
								</span>
							{else}
								<input type="hidden" value="0" id="customFilter" />
							{/if}
						</span>
					</div>
					<div class="col-md-2">
						<button type="button" class="btn btn-default loadFormFilterButton popoverTooltip" data-content="{vtranslate('LBL_LOAD_RECORDS_INFO',$MODULE)}">
							<span class="glyphicon glyphicon-filter"></span>&nbsp;
							<strong>{vtranslate('LBL_LOAD_RECORDS',$MODULE)}</strong>
						</button>
					</div>
					<div class="col-md-4">
						<div class="paginationDiv pull-right">
							{include file='Pagination.tpl'|@vtemplate_path:$MODULE VIEWNAME='related'}
						</div>
					</div>
				</div>
			</div>
			<div id="selectAllMsgDiv" class="alert-block msgDiv">
				<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($RELATED_MODULE->get('name'))}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
			</div>
			<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
				<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
			</div>
			<div class="contents-topscroll">
				<div class="topscroll-div">
					&nbsp;
				</div>
			</div>
			{include file=vtemplate_path('ListViewAlphabet.tpl',$RELATED_MODULE_NAME) MODULE_MODEL=$RELATED_MODULE}
			<div class="relatedContents contents-bottomscroll">
				<div class="bottomscroll-div">
					{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
					<table class="table table-bordered listViewEntriesTable">
						<thead>
							<tr class="listViewHeaders">
								<th width="4%">
									<input type="checkbox" title="{vtranslate('LBL_SELECT_ALL')}" id="listViewEntriesMainCheckBox"/>
								</th>
								{if $IS_FAVORITES}
									<th></th>
									{/if}
									{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th nowrap>
										{if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
											<a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
										{elseif $HEADER_FIELD->get('column') eq 'time_start'}
										{else}
											<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
												&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<span class="{$SORT_IMAGE}"></span>{/if}
											</a>
										{/if}
									</th>
								{/foreach}
								<th nowrap colspan="2">
									<a href="javascript:void(0);" class="noSorting">{vtranslate('Status', $RELATED_MODULE->get('name'))}</a>
								</th>
							</tr>
						</thead>
						{if $RELATED_MODULE->isQuickSearchEnabled()}
							<tr>
								<td>
									<a class="btn btn-default" data-trigger="listSearch" href="javascript:void(0);"><span class="glyphicon glyphicon-search"></span></a>
								</td>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<td>
										{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
										{if isset($SEARCH_DETAILS[$HEADER_FIELD->getName()])}
											{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
										{else}
											{assign var=SEARCH_INFO value=[]}
										{/if}
										{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME)
							FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE}
									</td>
								{/foreach}
								<td>
									<button type="button" class="btn btn-default removeSearchConditions">
										<span class="glyphicon glyphicon-remove"></button>
									</a>
								</td>
							</tr>
						{/if}
						{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
							<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
								<td width="4%" class="{$WIDTHTYPE}">
									<input type="checkbox" value="{$RELATED_RECORD->getId()}" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox"/>
								</td>
								{if $IS_FAVORITES}
									<td class="{$WIDTHTYPE} text-center text-center font-larger">
										{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
										<a class="favorites" data-state="{$RECORD_IS_FAVORITE}">
											<span title="{vtranslate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star alignMiddle {if !$RECORD_IS_FAVORITE}hide{/if}"></span>
											<span title="{vtranslate('LBL_ADD_TO_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star-empty alignMiddle {if $RECORD_IS_FAVORITE}hide{/if}"></span>
										</a>
									</td>
								{/if}
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
									<td nowrap class="{$WIDTHTYPE}">
										{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
											<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
										{elseif $RELATED_HEADERNAME eq 'access_count'}
											{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
										{elseif $RELATED_HEADERNAME eq 'time_start'}
										{else}
											{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
										{/if}
									</td>
								{/foreach}
								<td nowrap class="{$WIDTHTYPE}">
									<!--
									<span class="currentStatus btn-group">
										<span class="statusValue dropdown-toggle" data-toggle="dropdown">{vtranslate($RELATED_RECORD->get('status'),$MODULE)}</span>
										<span title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-arrow-down alignMiddle editRelatedStatus"></span>
										<ul class="dropdown-menu pull-right" style="left: -2px; position: relative;">
											{foreach key=STATUS_ID item=STATUS from=$STATUS_VALUES}
												<li id="{$STATUS_ID}" data-status="{vtranslate($STATUS, $MODULE)}">
													<a>{vtranslate($STATUS, $MODULE)}</a>
												</li>
											{/foreach}
										</ul>
									</span>
									-->
								</td>
								<td nowrap class="{$WIDTHTYPE}">
									<div class="pull-right actions">
										<span class="actionImages">
											<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
												{if $IS_EDITABLE}
												<a href='{$RELATED_RECORD->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>
												{/if}
												{if $IS_DELETABLE}
												<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
												{/if}
										</span>
									</div>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	{else}
		{include file='RelatedList.tpl'|@vtemplate_path}
	{/if}
{/strip}
