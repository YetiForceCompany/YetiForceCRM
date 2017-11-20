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
		{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
		<input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
		<input type="hidden" value="{$ORDER_BY}" id="orderBy">
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
		<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
		<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
		<div class="relatedHeader calendarRelatedHeader">
			<div class="btn-toolbar row">
				<div class="col-sm-6 col-md-6">
					{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
						{if {\App\Privilege::isPermitted($RELATED_MODULE_NAME, 'CreateView')} }
							<div class="btn-group paddingRight10">
								{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
								<button type="button" class="btn btn-default addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE_NAME} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
										{if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
										{if ($RELATED_LINK->isPageLoadLink())}
											{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
											data-url="{$RELATED_LINK->getUrl()}"
										{/if}
										{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<span class="glyphicon glyphicon-plus icon-white"></span>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
							</div>
						{/if}
					{/foreach}
					&nbsp;
					<div class="btn-group">
						<input class="switchBtn" type="checkbox" {if $TIME=='current'}checked{/if} title="{\App\Language::translate('LBL_CHANGE_ACTIVITY_TYPE')}" data-size="normal" data-label-width="5" data-handle-width="90" data-on-text="{\App\Language::translate('LBL_CURRENT')}" data-off-text="{\App\Language::translate('LBL_HISTORY')}">
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="pull-right">
						{if $RELATION_LIST_VIEW}
							<div class="pull-right paddingLeft5px">
								{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
								<input type="hidden" class="entityState" value="{if $RELATION_LIST_VIEW->has('entityState')}{$RELATION_LIST_VIEW->get('entityState')}{else}Active{/if}">
								<div class="dropdown dropdownEntityState">
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										{if $RELATION_LIST_VIEW->get('entityState') === 'Archived'}
											<span class="fa fa-archive"></span>
										{elseif $RELATION_LIST_VIEW->get('entityState') === 'Trash'}
											<span class="glyphicon glyphicon-trash"></span>
										{elseif $RELATION_LIST_VIEW->get('entityState') === 'All'}
											<span class="glyphicon glyphicon-menu-hamburger"></span>
										{else}
											<span class="fa fa-refresh"></span>
										{/if}
									</button>
									<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
										<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
											<a href="#" data-value="Active"><span class="fa fa-refresh"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}</a>
										</li>
										<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
											<a href="#" data-value="Archived"><span class="fa fa-archive"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}</a>
										</li>
										<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
											<a href="#" data-value="Trash"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}</a>
										</li>
										<li>
											<a href="#" data-value="All"><span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ALL')}</a>
										</li>
									</ul>
								</div>
							</div>
						{/if}
					</div>
					<div class="paginationDiv pull-right">
						{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE) VIEWNAME='related'}
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
				{assign var=FILENAME value="RelatedListContents.tpl"}
				{include file=\App\Layout::getTemplatePath($FILENAME, $RELATED_MODULE->get('name'))}
			</div>
		</div>
	</div>
{/strip}
