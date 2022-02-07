{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-RelatedList relatedContainer js-list__form" data-js="container">
		{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
		{assign var=INVENTORY_MODULE value=$RELATED_MODULE->isInventory()}
		<input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
		<input type="hidden" id="orderBy" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}">
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries" />
		<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit' />
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount' />
		<input type="hidden" id="autoRefreshListOnChange" value="{App\Config::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
		<input type="hidden" id="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}">
		<input type="hidden" class="js-empty-fields" data-js="value" value="{\App\Purifier::encodeHtml(\App\Json::encode($LOCKED_EMPTY_FIELDS))}" />
		<div class="relatedHeader calendarRelatedHeader mb-1">
			<div class="row">
				<div class="col-sm-6 col-md-6">
					{if $RELATED_LIST_LINKS['RELATEDLIST_VIEWS']|@count gt 0}
						<div class="btn-group pr-2 relatedViewGroup">
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
									<span class="{$BTN_ICON}"></span>
								{else}
									<span class="fas fa-list"></span>
								{/if}
								&nbsp;
								<span class="textHolder">{\App\Language::translate($TEXT_HOLDER, $MODULE_NAME)}</span>
								&nbsp;<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach item=RELATEDLIST_VIEW from=$RELATED_LIST_LINKS['RELATEDLIST_VIEWS']}
									<li>
										<a class="dropdown-item" href="#" data-view="{$RELATEDLIST_VIEW->get('view')}">
											{if $RELATEDLIST_VIEW->get('linkicon') neq ''}
												<span class="{$RELATEDLIST_VIEW->get('linkicon')}"></span>
												&nbsp;&nbsp;
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
							<div class="btn-group pr-2">
								{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
								<button type="button" class="btn btn-light addButton
									{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE_NAME} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
									{if $IS_SELECT_BUTTON eq true}
									data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
									{if ($RELATED_LINK->isPageLoadLink())}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
										data-url="{$RELATED_LINK->getUrl()}"
									{/if}
									{if $IS_SELECT_BUTTON neq true}name="addButton" {/if}>
									{if $IS_SELECT_BUTTON eq false}<span class="fas fa-plus"></span>{/if}
									{if $IS_SELECT_BUTTON eq true}<span class="fas fa-search"></span>{/if}
									&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong>
								</button>
							</div>
						{/if}
					{/foreach}
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-outline-primary {if $TIME eq 'current'}active{/if}">
							<input class="js-switch--calendar" type="radio" name="options" id="calendar-option1"
								title="{\App\Language::translate('LBL_CHANGE_ACTIVITY_TYPE')}"
								data-js="change"
								data-on-text="{App\Language::translate('LBL_CURRENT')}"
								autocomplete="off"
								{if $TIME eq 'current'}checked{/if}> {\App\Language::translate('LBL_CURRENT')}
						</label>
						<label class="btn btn-outline-primary {if $TIME neq 'current'}active{/if}">
							<input class="js-switch--calendar" type="radio" name="options" id="calendar-option2"
								title="{\App\Language::translate('LBL_CHANGE_ACTIVITY_TYPE')}"
								data-js="change"
								data-off-text="{App\Language::translate('LBL_HISTORY')}"
								autocomplete="off"
								{if $TIME neq 'current'}checked{/if}> {\App\Language::translate('LBL_HISTORY')}
						</label>
					</div>
				</div>
				<div class="col-12 col-sm-6 col-md-6">
					<div class="float-right">
						{if $VIEW_MODEL}
							<div class="float-right pl-1">
								{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
								<input type="hidden" class="entityState"
									value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}" />
								<div class="dropdown dropdownEntityState u-remove-dropdown-icon">
									<button class="btn btn-light dropdown-toggle" type="button" id="dropdownEntityState"
										data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
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
										<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};" {/if}>
											<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') == 'Active'} active{/if}"
												href="#" data-value="Active"><span class="fas fa-undo-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}
											</a>
										</li>
										<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};" {/if}>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Archived'} active{/if}"
												href="#" data-value="Archived"><span class="fas fa-archive"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
											</a>
										</li>
										<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};" {/if}>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'Trash'} active{/if}"
												href="#" data-value="Trash"><span class="fas fa-trash-alt"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
											</a>
										</li>
										<li>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') == 'All'} active{/if}"
												href="#" data-value="All"><span
													class="fas fa-bars"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_ALL')}
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
		{if $RELATED_VIEW === 'ListPreview'}
			<div class="relatedContents">
				<div class="d-flex">
					<input type="hidden" id="defaultDetailViewName"
						value="{App\Config::module($MODULE, 'defaultDetailViewName')}" />
					<div class="c-side-block c-side-block--left js-side-block js-fixed-scroll"
						data-js="css: height;/scroll">
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
					<div class="c-detail-preview js-detail-preview ">
						<iframe class="listPreviewframe border1px" frameborder="0"></iframe>
					</div>
					<div class="c-side-block c-side-block--right js-side-block js-fixed-scroll"
						data-js="css: height;/scroll">
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
