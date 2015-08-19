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
    <div class="relatedContainer">
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
        <div class="relatedHeader ">
            <div class="btn-toolbar row">
                <div class="col-md-8">
                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button type="button" class="btn btn-default addButton
									{if $IS_SELECT_BUTTON eq true} selectRelation {/if} moduleColor_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
									{if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
									{if ($RELATED_LINK->isPageLoadLink())}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
										data-url="{$RELATED_LINK->getUrl()}"
									{/if}
									{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<span class="glyphicon glyphicon-plus icon-white"></span>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
						</div>
					{/foreach}
					&nbsp;
					<div class="btn-group">{if $EMPTY}<button class="btn btn-success" id="generateFromTpl" type="button">{vtranslate('GENERATE_FROM_TPL', 'OSSProjectTemplates')}</button>{/if}</div>
				</div>
				<div class="col-md-4">
					<span class="row">
						<div class="col-md-4 pushDown">
							<span class="pull-right pageNumbers alignTop" data-placement="bottom" data-original-title="" style="margin-top: -5px">
								{if !empty($RELATED_RECORDS)} {$PAGING->getRecordStartRange()} {vtranslate('LBL_TO_LC', $RELATED_MODULE->get('name'))} {$PAGING->getRecordEndRange()}{/if}
							</span>
						</div>
						<div class="col-md-7 pull-right">
							<span class="btn-group pull-right">
								<button class="btn btn-default" id="relatedListPreviousPageButton" {if !$PAGING->isPrevPageExists()} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-left"></span></button>
								<button class="btn btn-default dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
									<span class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></span>
								</button>
								<ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown">
									<li>
										<div class="">
											<div class="col-md-4 recentComments textAlignCenter pushUpandDown2per"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></div>
											<div class="col-md-3 recentComments">
												<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGING->getCurrentPage()}"/>
											</div>
											<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
												{vtranslate('LBL_OF',$moduleName)}
											</div>
											<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per" id="totalPageCount">{$PAGE_COUNT}</div>
										</div>
									</li>
								</ul>
								<button class="btn btn-default" id="relatedListNextPageButton" {if (!$PAGING->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-right"></span></button>
							</span>
						</div>
					</span>
				</div>
			</div>
		</div>
		<div class="contents-topscroll">
			<div class="topscroll-div">
				&nbsp;
			</div>
		</div>
		<div class="relatedContents contents-bottomscroll">
			<div class="bottomscroll-div">
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<table class="table table-bordered listViewEntriesTable">
					<thead>
						<tr class="listViewHeaders">
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap class="{$WIDTHTYPE}">
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
						</tr>
					</thead>
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
						<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
								<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
									{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
										<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
									{elseif $RELATED_HEADERNAME eq 'access_count'}
										{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
									{elseif $RELATED_HEADERNAME eq 'time_start'}
									{else}
										{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
									{/if}
									{if $HEADER_FIELD@last}
									</td><td nowrap class="{$WIDTHTYPE}">
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
								{/if}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</table>
			</div>
		</div>
	</div>
{/strip}

<script type="text/javascript" src="layouts/vlayout/modules/Potentials/resources/PotentialsRelatedList.js"></script>
