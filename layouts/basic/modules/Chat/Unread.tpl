{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Unread">
		{foreach item=MESSAGE from=\App\Chat::getUnread()}
			{assign var=LAST_ROOM_NAME value=''}
			{foreach item=ROW from=$MESSAGE}
				{if $LAST_ROOM_NAME!=$ROW['room_name']}
					<div class="text-uppercase color-blue-400">{$ROW['room_name']}</div>
					{assign var=LAST_ROOM_NAME value=$ROW['room_name']}
				{/if}
				{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
			{/foreach}
		{/foreach}

		{*
		{assign var=MESSAGE value=\App\Chat::getUnreadByType('global')}
		{foreach item=ROW from=$MESSAGE}
			{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
		{/foreach}
		*}
	</div>
{/strip}