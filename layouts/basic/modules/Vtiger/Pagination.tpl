{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($VIEWNAME)}
		{assign var=VIEWNAME value='list'}
	{/if}
	{if empty($LISTVIEW_COUNT)}
		{assign var=LISTVIEW_COUNT value=0}
	{/if}
	<nav class="tpl-Pagination" role="navigation" aria-label="{\App\Language::translate('LBL_PAGINATION_NAV')}">
		<ul class="js-pagination-list pagination m-0"
			{if isset($LISTVIEW_COUNT)}data-total-count="{$LISTVIEW_COUNT}" {/if} data-js="data">
			<li class="js-page--set page-item {if !$PAGING_MODEL->isPrevPageExists() OR $PAGE_NUMBER eq 1} disabled {/if} pageNumber firstPage" aria-label="{\App\Language::translate('LBL_GO_TO_FIRST_PAGE')}"
				data-id="1"
				data-js="data">
				<a class="page-link" href="#"><span
						class="fas fa-fast-backward mr-1 d-inline-block d-sm-none"></span><span
						class="d-none d-sm-inline">{\App\Language::translate('LBL_FIRST')}</span></a>
			</li>
			<li class="page-item {if !$PAGING_MODEL->isPrevPageExists() OR $PAGE_NUMBER eq 1}disabled{/if}">
				<a class="js-page--previous page-link" aria-label="{\App\Language::translate('LBL_PREV')}"
					id="{$VIEWNAME}ViewPreviousPageButton" data-js="click" href="#">
					<span aria-hidden="true">&laquo;</span>
					<span class="sr-only">Previous</span>
				</a>
			</li>
			{if $PAGE_COUNT neq 0}
				{assign var=PAGIN_TO value=$START_PAGIN_FROM+4}
				{for $PAGE_INDEX=$START_PAGIN_FROM to $PAGIN_TO}
					{if $PAGE_INDEX eq $PAGE_COUNT || $PAGE_INDEX eq $PAGIN_TO}
						{if $PAGE_COUNT > 5}
							<li class="page-item {if $PAGE_COUNT eq 1} disabled{/if}">
								<a class="page-link" id="dLabel" data-target="#" data-toggle="dropdown" role="button"
									href="#" aria-expanded="true">
									...
								</a>
								<div class="js-page--jump-drop-down dropdown-menu listViewBasicAction" data-js="click"
									aria-labelledby="dLabel" id="{$VIEWNAME}ViewPageJumpDropDown">
									<a class="dropdown-item">
										<div class="row">
											<div class="col-md-3 p-0 textAlignCenter pushUpandDown2per">
												<span>{\App\Language::translate('LBL_PAGE')}</span>
											</div>
											<div class="col-md-3 p-0">
												<input type="text" id="pageToJump"
													class="js-page-jump listViewPagingInput u-h-input-text textAlignCenter form-control"
													title="{\App\Language::translate('LBL_LISTVIEW_PAGE_JUMP')}"
													value="{$PAGE_NUMBER}"
													data-js="keypress" />
											</div>
											<div class="col-md-2 p-0 textAlignCenter pushUpandDown2per">
												{\App\Language::translate('LBL_OF')}
											</div>
											<div class="js-page--total col-md-2 p-0 pushUpandDown2per textAlignCenter"
												id="totalPageCount" data-js="text">{$PAGE_COUNT}</div>
										</div>
									</a>
								</div>
							</li>
						{/if}
						{break}
					{/if}
					<li class="js-page--set page-item pageNumber{if $PAGE_NUMBER eq $PAGE_INDEX} active disabled{/if}"
						data-id="{$PAGE_INDEX}" data-js="click">
						<a class="page-link" {if $PAGE_INDEX === ($PAGE_COUNT - 1)}aria-label="{\App\Language::translate('LBL_PREV_PAGE')} {$PAGE_INDEX}" {else}aria-label="{\App\Language::translate('LBL_GO_TO_PAGE_NUMBER')} {$PAGE_INDEX}" {/if} href="#">{$PAGE_INDEX}</a>
					</li>
				{/for}
			{/if}
			{if $PAGE_INDEX <= $PAGE_COUNT}
				<li class="js-page--set pageNumber{if $PAGE_NUMBER eq $PAGE_COUNT} active disabled{/if}" data-js="click"
					data-id="{$PAGE_COUNT}">
					<a class="page-link" aria-current="true" aria-label="{\App\Language::translate('LBL_CURRENT_PAGE')} {$PAGE_COUNT}" href="#">{$PAGE_COUNT}</a>
				</li>
			{/if}
			<li class="js-next-page page-item {if (!$PAGING_MODEL->isNextPageExists())}disabled{/if}"
				id="{$VIEWNAME}ViewNextPageButton" data-js="click">
				<a class="page-link" href="#" aria-label="{\App\Language::translate('LBL_NEXT')}">
					<span aria-hidden="true">&raquo;</span>
					<span class="sr-only">Next</span>
				</a>
			</li>
			{if empty($LISTVIEW_COUNT) && $PAGING_MODEL->isNextPageExists()}
				<li class="js-count-number-records page-item js-popover-tooltip" data-js="popover|click"
					{assign var="TRANSLATE_DATA" value="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}"}
					id="totalCountBtn" data-content="{$TRANSLATE_DATA}">
					<a class="page-link" aria-label="{$TRANSLATE_DATA}" href="#"><span class="fas fa-signal"></span></a>
				</li>
			{/if}
			{if !empty($LISTVIEW_COUNT)}
				<li class="js-page--set page-item {if $PAGE_NUMBER eq $PAGE_COUNT or (!$PAGING_MODEL->isNextPageExists())} disabled {/if} pageNumber lastPage"
					data-id="{$PAGE_COUNT}" data-js="click">
					<a class="page-link" href="#"><span
							class="fas fa-fast-forward mr-1 d-inline-block d-sm-none"></span><span
							class="d-none d-sm-inline">{\App\Language::translate('LBL_LAST')}</span></a>
				</li>
			{/if}
			<li class="page-item text-muted">
				<a class="page-link pageNumbersText">
					<span class="js-popover-tooltip d-block d-sm-none" tabindex="0" data-trigger="focus"
						data-js="popover" data-placement="top"
						data-content="{$PAGING_MODEL->getRecordStartRange()} {\App\Language::translate('LBL_TO_LC')} {$PAGING_MODEL->getRecordEndRange()}
					{if !empty($LISTVIEW_COUNT)} ({$LISTVIEW_COUNT}){/if}">
						<span class="fas fa-info-circle"
							title="{App\Language::translate('LBL_SHOW_INVENTORY_ROW')}"></span>
					</span>
					<span class="d-none d-sm-inline">{$PAGING_MODEL->getRecordStartRange()} {\App\Language::translate('LBL_TO_LC')} {$PAGING_MODEL->getRecordEndRange()} {if !empty($LISTVIEW_COUNT)} ({$LISTVIEW_COUNT}){/if}</span>
				</a>
			</li>
		</ul>
	</nav>
{/strip}
