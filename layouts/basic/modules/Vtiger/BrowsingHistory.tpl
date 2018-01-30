{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<ul class="dropdown-menu float-right historyList" role="menu">
	{foreach item=HISTORY from=$BROWSING_HISTORY}
		{if isset($HISTORY['viewToday'])}
			<li class="item selectorHistory">{\App\Language::translate('LBL_TODAY')}</li>
		{elseif isset($HISTORY['viewYesterday'])}
			<li class="item selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</li>
		{elseif isset($HISTORY['viewOlder'])}
			<li class="item selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</li>
		{/if}
		<li class="item">
			<a href="{$HISTORY['url']}">
				{if $HISTORY['hour']}
					<span class="historyHour">{$HISTORY['date']}</span> 
				{else}
					{$HISTORY['date']}
				{/if} 
				{" | "} 
				{$HISTORY['title']}
			</a>
		</li>
	{/foreach}
	<li class="divider"></li>
	<li><a class="clearHistory" href="#" onclick="app.clearBrowsingHistory();">{\App\Language::translate('LBL_CLEAR_HISTORY')}</a></li>
</ul>
{/strip}
