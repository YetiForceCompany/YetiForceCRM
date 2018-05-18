{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<ul class="dropdown-menu historyList" aria-labelledby="showHistoryBtn">
		{foreach item=HISTORY from=$BROWSING_HISTORY}
			{if isset($HISTORY['viewToday'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_TODAY')}</h6>
			{elseif isset($HISTORY['viewYesterday'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</h6>
			{elseif isset($HISTORY['viewOlder'])}
				<h6 class="dropdown-header selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</h6>
			{/if}
			<li class="item dropdown-item" href="{$HISTORY['url']}">
				{if $HISTORY['hour']}
					<span class="historyHour">{$HISTORY['date']}</span>
				{else}
					{$HISTORY['date']}
				{/if}
				{" | "}
				{$HISTORY['title']}
			</li>
		{/foreach}
		<div class="dropdown-divider"></div>
		<li class="dropdown-item clearHistory">{\App\Language::translate('LBL_CLEAR_HISTORY')}</li>
	</ul>
{/strip}
