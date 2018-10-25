{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Items -->
	{if isset($PARTICIPANTS) && $PARTICIPANTS}
		<input type="hidden" name="participants" value="{\App\Purifier::encodeHtml(\App\Json::encode($PARTICIPANTS))}"
			   class="js-participants-data" data-js="data">
	{/if}
	{foreach item=ROW key=$KEY_ITEM from=$CHAT_ENTRIES}
		{if isset($SHOW_MORE_BUTTON) && $SHOW_MORE_BUTTON && $KEY_ITEM == 0 }
			<button type="button" class="btn btn-success d-block mr-auto ml-auto mt-3 js-load-more"
					data-mid="{$ROW['id']}"
					data-js="click">
				{\App\Language::translate('LBL_MORE_BTN')}
			</button>
			{continue}
		{/if}
		{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
	{/foreach}
	<!-- /tpl-Chat-Items -->
{/strip}
