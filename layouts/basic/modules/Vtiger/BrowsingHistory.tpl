{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dropdown-menu historyList" role="menu">
		{foreach item=HISTORY from=$BROWSING_HISTORY}
			{if isset($HISTORY['viewToday'])}
				<a class="dropdown-item item selectorHistory">{\App\Language::translate('LBL_TODAY')}</a>
			{elseif isset($HISTORY['viewYesterday'])}
				<a class="dropdown-item item selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</a>
			{elseif isset($HISTORY['viewOlder'])}
				<a class="dropdown-item item selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</a>
			{/if}

			<a class="item dropdown-item" href="{$HISTORY['url']}">
				{if $HISTORY['hour']}
					<span class="historyHour">{$HISTORY['date']}</span>
				{else}
					{$HISTORY['date']}
				{/if}
				{" | "}
				{$HISTORY['title']}
			</a>

		{/foreach}
		<a class="dropdown-item dropdown-divider"></a>
		<a class="dropdown-item clearHistory" href="#" onclick="app.clearBrowsingHistory();">{\App\Language::translate('LBL_CLEAR_HISTORY')}</a>
	</div>
{/strip}
