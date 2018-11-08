{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Unread">
		{assign var=CNT_MESSAGE value=0}
		{foreach item=MESSAGE from=\App\Chat::getUnread()}
			{assign var=LAST_ROOM_NAME value=''}
			{foreach item=ROW from=$MESSAGE}
				{assign var=CNT_MESSAGE value=$CNT_MESSAGE+1}
				{if $LAST_ROOM_NAME!=$ROW['room_name']}
					<div class="text-uppercase color-blue-400">{$ROW['room_name']}</div>
					{assign var=LAST_ROOM_NAME value=$ROW['room_name']}
				{/if}
				{include file=\App\Layout::getTemplatePath('Item.tpl', 'Chat')}
			{/foreach}
		{/foreach}
		{if $CNT_MESSAGE===0}
			<div class="alert alert-info mt-1">
				{\App\Language::translate('LBL_NO_MESSAGES',$MODULE_NAME)}
			</div>
		{/if}
	</div>
{/strip}
