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
	<div class="RelatedList relatedContainer">
		{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
		{assign var=INVENTORY_MODULE value=$RELATED_MODULE->isInventory()}
		<input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}"/>
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}"/>
		<input type="hidden" value="{$ORDER_BY}" id="orderBy"/>
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder"/>
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
		<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
		<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
		<input type="hidden" class="relatedView" value="{$RELATED_VIEW}">
		<div class="relatedHeader my-1">
			<div class="d-inline-flex flex-wrap w-100 justify-content-between">
				<div class="u-w-sm-down-100 d-flex flex-wrap flex-sm-nowrap mb-1 mb-md-0">
					{if $RELATED_LIST_LINKS['RELATEDLIST_VIEWS']|@count gt 0}
						<div class="btn-group mr-sm-1 relatedViewGroup c-btn-block-sm-down mb-1 mb-sm-0">
							{assign var=TEXT_HOLDER value=''}
							{foreach item=RELATEDLIST_VIEW from=$RELATED_LIST_LINKS['RELATEDLIST_VIEWS']}
								{if $RELATED_VIEW == $RELATEDLIST_VIEW->get('view')}
									{assign var=TEXT_HOLDER value=$RELATEDLIST_VIEW->getLabel()}
									{if $RELATEDLIST_VIEW->get('linkicon') neq ''}
										{assign var=BTN_ICON value=$RELATEDLIST_VIEW->get('linkicon')}
									{/if}
								{/if}
							{/foreach}
							<button class="btn btn-light dropdown-toggle relatedViewBtn" data-toggle="dropdown">
								{if $BTN_ICON}
									<span class="{$BTN_ICON} mr-1"></span>
								{else}
									<span class="fas fa-list mr-1"></span>
								{/if}
								<span class="textHolder">{\App\Language::translate($TEXT_HOLDER, $MODULE_NAME)}</span>
							</button>
							<ul class="dropdown-menu">
								{foreach item=RELATEDLIST_VIEW from=$RELATED_LIST_LINKS['RELATEDLIST_VIEWS']}
									<li>
										<a class="dropdown-item" href="#" data-view="{$RELATEDLIST_VIEW->get('view')}">
											{if $RELATEDLIST_VIEW->get('linkicon') neq ''}
												<span class="{$RELATEDLIST_VIEW->get('linkicon')} mr-1"></span>
											{/if}
											{\App\Language::translate($RELATEDLIST_VIEW->getLabel(), $MODULE_NAME)}
										</a>
									</li>
								{/foreach}
							</ul>
						</div>
					{/if}
					{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
						{if {\App\Privilege::isPermitted($RELATED_MODULE_NAME, 'CreateView')} }
							<div class="btn-group mr-md-1 c-btn-block-sm-down">
								{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
								<button type="button" class="btn btn-light addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE_NAME} {if !empty($RELATED_LINK->linkqcs)}quickCreateSupported{/if}"
										{if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
										{if ($RELATED_LINK->isPageLoadLink())}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
									data-url="{$RELATED_LINK->getUrl()}"
										{else}
									onclick='{$RELATED_LINK->getUrl()|substr:strlen("javascript:")};'
										{/if}
										{if $IS_SELECT_BUTTON neq true && stripos($RELATED_LINK->getUrl(), 'javascript:') !== 0}name="addButton"{/if}>
									{if $IS_SELECT_BUTTON eq false}
										<span class="{$RELATED_LINK->getIcon()} mr-1"></span>
									{/if}
									{if $IS_SELECT_BUTTON eq true}<span class="fas fa-search mr-1"></span>{/if}
									{$RELATED_LINK->getLabel()}
								</button>
							</div>
						{/if}
					{/foreach}
				</div>
				<div class="d-flex flex-wrap u-w-sm-down-100 justify-content-between justify-content-md-end">
					<div class="paginationDiv">
						{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE) VIEWNAME='related'}
					</div>
					{if $VIEW_MODEL}
						<div class="ml-1">
							{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
							<input type="hidden" class="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}">
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
										<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') == 'Active'} active{/if}" href="#" data-value="Active"><span class="fas fa-undo-alt mr-2"></span>{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}
										</a>
									</li>
									<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
										<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Archived'} active{/if}" href="#" data-value="Archived"><span class="fas fa-archive mr-2"></span>{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
										</a>
									</li>
									<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
										<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Trash'} active{/if}" href="#" data-value="Trash"><span class="fas fa-trash-alt mr-2"></span>{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
										</a>
									</li>
									<li>
										<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'All'} active{/if}" href="#" data-value="All"><span class="fas fa-bars mr-2"></span>{\App\Language::translate('LBL_ALL')}
										</a>
									</li>
								</ul>
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
		{if $RELATED_VIEW === 'ListPreview'}
			<div class="relatedContents">
				<div class="d-flex">
					<input type="hidden" id="defaultDetailViewName" value="{AppConfig::module($MODULE, 'defaultDetailViewName')}"/>
					<div class="c-side-block c-side-block--left js-side-block js-fixed-scroll" data-js="css: height;/scroll">
						<div class="u-rotate-90">
							<div class="font-weight-bold text-center">{\App\Language::translate('LBL_VIEW_LIST')}</div>
						</div>
					</div>
					<div class="c-list-preview js-list-preview js-fixed-scroll" data-js="scroll">
						<div class="c-list-preview__content js-list-preview--scroll" data-js="perfectScrollbar">
							<div id="recordsList">
								{include file=\App\Layout::getTemplatePath("RelatedListContents.tpl", $RELATED_MODULE->get('name'))}
							</div>
						</div>
					</div>
					<div class="c-detail-preview js-detail-preview">
						<iframe class="listPreviewframe" frameborder="0"></iframe>
					</div>
					<div class="c-side-block c-side-block--right js-side-block js-fixed-scroll" data-js="css: height;/scroll">
						<div class="u-rotate-90">
							<div class="font-weight-bold text-center">{\App\Language::translate('LBL_VIEW_DETAIL')}</div>
						</div>
					</div>
				</div>
			</div>
		{else}
			<div class="relatedContents">
				{include file=\App\Layout::getTemplatePath("RelatedListContents.tpl", $RELATED_MODULE->get('name'))}
			</div>
		{/if}
	</div>
{/strip}
