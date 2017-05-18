{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} --!>*}
{strip}
<ul class="dropdown-menu pull-right historyList" role="menu">
	{foreach item=$HISTORY from=$BROWSING_HISTORY}
		{if isset($HISTORY['viewToday'])}
			<li class="selectorHistory">{\App\Language::translate('LBL_TODAY')}</li>
		{elseif isset($HISTORY['viewYesterday'])}
			<li class="selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</li>
		{elseif isset($HISTORY['viewOlder'])}
			<li class="selectorHistory">{\App\Language::translate('LBL_YESTERDAY')}</li>
		{/if}
		<li>
			<a href="{$HISTORY['url']}">
				{if $HISTORY['hour']}
					<span class="historyHour">{$HISTORY['view_date']|date_format:"H:i"}</span> 
				{else}
					{$HISTORY['view_date']}
				{/if} 
				{" | "} 
				{$HISTORY['page_title']}
			</a>
		</li>
	{/foreach}
	<li class="divider"></li>
	<li><a class="clearHistory" href="index.php?clearBrowsingHistory=1">{\App\Language::translate('LBL_CLEAR_HISTORY')}</a></li>
</ul>
{/strip}
