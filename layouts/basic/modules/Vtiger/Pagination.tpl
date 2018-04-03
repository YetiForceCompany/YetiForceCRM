{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($VIEWNAME)}
		{assign var=VIEWNAME value='list'}
	{/if}
	<nav class="Pagination" aria-label="Page navigation">
		<ul class="pagination m-0" data-total-count="{$LISTVIEW_COUNT}">
			<li class="page-item {if $PAGE_NUMBER eq 1} disabled {/if} pageNumber firstPage" data-id="1" >
				<a class="page-link" href="#">{\App\Language::translate('LBL_FIRST')}</a>
			</li>
			<li  class="page-item">
				<a class="page-link {if !$PAGING_MODEL->isPrevPageExists() OR $PAGE_NUMBER eq 1} disabled {/if}" id="{$VIEWNAME}ViewPreviousPageButton" href="#">
					<span aria-hidden="true">&laquo;</span>
					<span class="sr-only">Previous</span>
				</a>
			</li>
			{if $PAGE_COUNT neq 0}
				{assign var=PAGIN_TO value=$START_PAGIN_FROM+4}
				{for $PAGE_INDEX=$START_PAGIN_FROM to $PAGIN_TO}
					{if $PAGE_INDEX eq $PAGE_COUNT || $PAGE_INDEX eq $PAGIN_TO}
						{if $PAGE_COUNT > 5}
							<li class="page-item {if $PAGE_COUNT eq 1} disabled {/if}">
								<a class="page-link" id="dLabel" data-target="#" data-toggle="dropdown" role="button" href="#" aria-expanded="true">
									...
								</a>
								<ul class="dropdown-menu listViewBasicAction" aria-labelledby="dLabel" id="{$VIEWNAME}ViewPageJumpDropDown">
									<li class="dropdown-item">
										<div>
											<div class="col-md-3 recentComments textAlignCenter pushUpandDown2per"><span>{\App\Language::translate('LBL_PAGE')}</span></div>
											<div class="col-md-3 recentComments">
												<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter form-control" title="{\App\Language::translate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGE_NUMBER}" />
											</div>
											<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
												{\App\Language::translate('LBL_OF')}
											</div>
											<div class="col-md-2 recentComments pushUpandDown2per textAlignCenter" id="totalPageCount">{$PAGE_COUNT}</div>
										</div>
									</li>
								</ul>
							</li>
						{/if}
						{break}
					{/if}
					<li class="page-item pageNumber{if $PAGE_NUMBER eq $PAGE_INDEX} active disabled{/if}" data-id="{$PAGE_INDEX}">
						<a class="page-link" href="#">{$PAGE_INDEX}</a>
					</li>
				{/for}
			{/if}
			{if $PAGE_INDEX <= $PAGE_COUNT}
				<li class="pageNumber{if $PAGE_NUMBER eq $PAGE_COUNT} active disabled{/if}" data-id="{$PAGE_COUNT}">
					<a class="page-link" href="#">{$PAGE_COUNT}</a>
				</li>
			{/if}
			<li class="page-item {if (!$PAGING_MODEL->isNextPageExists())} disabled {/if}" id="{$VIEWNAME}ViewNextPageButton">
				<a class="page-link" href="#" aria-label="Next">
					<span aria-hidden="true">&raquo;</span>
					<span class="sr-only">Next</span>
				</a>
			</li>
			{if !$LISTVIEW_COUNT && $PAGING_MODEL->isNextPageExists()}
				<li class="page-item js-popover-tooltip" data-js="popover" id="totalCountBtn" data-content="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}" >
					<a class="page-link" href="#"><span class="fas fa-signal"></span></a>
				</li>
			{/if}
			{if $LISTVIEW_COUNT}
				<li class="page-item {if $PAGE_NUMBER eq $PAGE_COUNT or (!$PAGING_MODEL->isNextPageExists())} disabled {/if} pageNumber lastPage" data-id="{$PAGE_COUNT}" >
					<a class="page-link" href="#">{\App\Language::translate('LBL_LAST')}</a>
				</li>
			{/if}
			<li class="page-item disabled">
				<a class="page-link pageNumbersText">
					{$PAGING_MODEL->getRecordStartRange()} {\App\Language::translate('LBL_TO_LC')} {$PAGING_MODEL->getRecordEndRange()}
					{if $LISTVIEW_COUNT} ({$LISTVIEW_COUNT}){/if}
				</a>
			</li>
		</ul>
	</nav>
{/strip}
