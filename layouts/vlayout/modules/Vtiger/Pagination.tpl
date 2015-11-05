{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<nav>		
	<ul class="pagination">
		<li class="{if $PAGE_NUMBER eq 1} disabled {/if} pageNumber firstPage" data-id="1" >
			<span aria-hidden="true">{vtranslate('LBL_FIRST', $MODULE)}</span>
		</li>
		<li class="{if !$PAGING_MODEL->isPrevPageExists() OR $PAGE_NUMBER eq 1} disabled {/if}" id="listViewPreviousPageButton">
			<span aria-hidden="true">&laquo;</span>
		</li>	
		{if $PAGE_COUNT neq 0}
			{assign var=PAGIN_TO value=$START_PAGIN_FROM+4}
			{for $PAGE_INDEX=$START_PAGIN_FROM to $PAGIN_TO}
				{if $PAGE_INDEX eq $PAGE_COUNT || $PAGE_INDEX eq $PAGIN_TO}
					{if $PAGE_COUNT > 5}
					<li {if $PAGE_COUNT eq 1} disabled {/if} >
						<a id="dLabel" data-target="#" data-toggle="dropdown" role="button" aria-expanded="true">
							...
						</a>
						<ul class="dropdown-menu listViewBasicAction" aria-labelledby="dLabel" id="listViewPageJumpDropDown">
							<li>
								<div>
									<div class="col-md-3 recentComments textAlignCenter pushUpandDown2per"><span>{vtranslate('LBL_PAGE',$moduleName)}</span></div>
									<div class="col-md-3 recentComments">
										<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter form-control" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGE_NUMBER}"/>
									</div>
									<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
										{vtranslate('LBL_OF',$moduleName)}
									</div>
									<div class="col-md-2 recentComments pushUpandDown2per textAlignCenter" id="totalPageCount">{$PAGE_COUNT}</div>
								</div>
							</li>
						</ul>
					</li>
					{/if}
					{break}
				{/if}
				<li class="pageNumber {if $PAGE_NUMBER eq $PAGE_INDEX} active disabled {/if}" data-id="{$PAGE_INDEX}"><a>{$PAGE_INDEX}</a></li>
			{/for}
		{/if}
		{if $PAGE_INDEX <= $PAGE_COUNT}
			<li class="pageNumber {if $PAGE_NUMBER eq $PAGE_COUNT} active disabled {/if}" data-id="{$PAGE_COUNT}"><a>{$PAGE_COUNT}</a></li>
		{/if}

		<li class="{if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_NUMBER eq $PAGE_COUNT)} disabled {/if}" id="listViewNextPageButton">
			<span aria-hidden="true">&raquo;</span>
		</li>
		<li class="{if $PAGE_NUMBER eq $PAGE_COUNT or (!$PAGING_MODEL->isNextPageExists())} disabled {/if} pageNumber lastPage" data-id="{$PAGE_COUNT}" >
			<span aria-hidden="true">{vtranslate('LBL_LAST', $MODULE)}</span>
		</li>
	</ul>
	<ul class="pageInfo">
		<li>
			<span>
				<span class="pageNumbersText" style="padding-right:5px">{if $LISTVIEW_ENTRIES_COUNT}{$PAGING_MODEL->getRecordStartRange()} 
					{vtranslate('LBL_TO_LC', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{else}<span>&nbsp;</span>{/if} ({$LISTVIEW_COUNT})</span>
			</span>
		</li>
	</ul>
</nav>
{/strip}
