{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Items -->
	{if isset($PARTICIPANTS) && $PARTICIPANTS}
		<input type="hidden" name="participants" value="{\App\Purifier::encodeHtml(\App\Json::encode($PARTICIPANTS))}"
			   class="js-participants-data" data-js="data">
	{/if}
	{foreach item=ROW key=$KEY_ITEM from=$CHAT_ENTRIES}
		{if isset($SHOW_MORE_BUTTON) && $SHOW_MORE_BUTTON && $KEY_ITEM == 0 }
			<button type="button"
					class="btn btn-sm btn-light text-secondary col-2 d-block mr-auto ml-auto mt-3 js-load-more"
					data-mid="{$ROW['id']}"
					data-js="click|remove">
				<span class="fas fa-angle-double-up mr-2"></span>
				{\App\Language::translate('LBL_EARLIER', 'Chat')|upper}
			</button>
			{continue}
		{/if}
		{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
	{/foreach}
	<!-- /tpl-Chat-Items -->
{/strip}
