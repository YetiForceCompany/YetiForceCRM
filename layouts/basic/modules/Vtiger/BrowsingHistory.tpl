{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-BrowsingHistory dropdown-menu historyList js-scrollbar" aria-labelledby="showHistoryBtn"
		role="list" data-js="perfectscrollbar">
		{foreach item=HISTORY from=$BROWSING_HISTORY}
			{if isset($HISTORY['viewToday'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_TODAY')}</h6>
			{elseif isset($HISTORY['viewYesterday'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</h6>
			{elseif isset($HISTORY['viewOlder'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</h6>
			{/if}
			<a class="item dropdown-item" href="/{$HISTORY['url']}" role="listitem">
				{if $HISTORY['hour']}
					<span class="historyHour">{$HISTORY['date']}</span>
				{else}
					{$HISTORY['date']}
				{/if}
				{" | "}
				{$HISTORY['title']}
			</a>
		{/foreach}
		<div class="dropdown-divider"></div>
		<a class="dropdown-item js-clear-history" data-js="click" href="#"
			role="listitem">{\App\Language::translate('LBL_CLEAR_HISTORY')}</a>
	</div>
{/strip}
