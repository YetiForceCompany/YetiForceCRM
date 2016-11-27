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
    <div class="relatedContainer">
        <input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
		<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
        <div class="relatedHeader ">
            <div class="btn-toolbar row">
                <div class="col-md-6">

                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group paddingRight10">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button type="button" class="btn btn-default
									{if $IS_SELECT_BUTTON eq true} selectRelation {/if} moduleColor_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
									{if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
									{if $RELATED_LINK->isPageLoadLink()}onclick="window.location.href='{$RELATED_LINK->getUrl()}'"{/if}
									>{if $IS_SELECT_BUTTON eq false}<span class="glyphicon glyphicon-plus"></span>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
						</div>
					{/foreach}
					&nbsp;
				</div>
				<div class="col-md-6">
					<div class="paginationDiv pull-right">
						{include file='Pagination.tpl'|@vtemplate_path:$MODULE VIEWNAME='related'}
					</div>
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
								<th nowrap {if $HEADER_FIELD@last} colspan="2" {/if}>
									<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('name')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
										&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}<span class="{$SORT_IMAGE}"></span>{/if}
									</a>
								</th>
							{/foreach}
						</tr>
					</thead>
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
						{assign var=BASE_CURRENCY_DETAILS value=$RELATED_RECORD->getBaseCurrencyDetails()}
						<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
							{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
								{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
								<td nowrap class="{$WIDTHTYPE}">
									{if $HEADER_FIELD->get('name') == 'listprice'}
										{CurrencyField::convertToUserFormat($RELATED_RECORD->get($HEADER_FIELD->get('name')))}
										{assign var="LISTPRICE" value=$RELATED_RECORD->get($HEADER_FIELD->get('name'))}
									{else if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
										<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
									{else}
										{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
									{/if}
									{if $HEADER_FIELD@last}
									</td><td nowrap class="{$WIDTHTYPE}">
										<div class="pull-right actions">
											<span class="actionImages">
												<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
												<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
												   class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
													<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
												</a>
												<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
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
