{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Detail-Chat {if !$CHAT->isRoomExists()}hide {/if} js-container-chat o-chat__detail u-min-h-85vh">
		{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
	</div>
{/strip}
