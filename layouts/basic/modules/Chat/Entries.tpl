{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Items -->
	{if isset($PARTICIPANTS) && $PARTICIPANTS}
		<input type="hidden" name="participants" value="{\App\Purifier::encodeHtml(\App\Json::encode($PARTICIPANTS))}"
			   class="js-participants-data" data-js="data">
	{/if}
	{foreach item=ROW from=$CHAT_ENTRIES}
		{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
	{/foreach}
	<!-- /tpl-Chat-Items -->
{/strip}
