{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Item js-chat-item c-chat__item chatItem {if \App\User::getCurrentUserId() == $ROW['userid']}active {/if}"
		 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
		<div class="float-right">
			<small>
				{$ROW['created']}
			</small>
		</div>
		<div class="author js-author" data-role-name="{$ROW['role_name']}" data-js="data">
			{assign var=IMAGE value=$ROW['image']}
			{assign var=IS_IMAGE value=isset($IMAGE['url'])}
			<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}" class="mr-2{if !$IS_IMAGE} hide{/if}"
				 alt="{$ROW['user_name']}"
				 title="{$ROW['user_name']}"/>
			<span class="fas fa-user userImage{if $IS_IMAGE} hide{/if}"></span>
			<b class="js-user-name" data-js="data">{$ROW['user_name']}</b>
		</div>
		<div class="messages">{\App\Purifier::decodeHtml($ROW['messages'])}</div>
	</div>
{/strip}
