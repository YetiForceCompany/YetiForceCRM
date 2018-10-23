{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Item js-chat-item chatItem {if \App\User::getCurrentUserId() == $ROW['userid']}active {/if}"
		 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
		<div class="float-right">
			<small>
				{$ROW['created']}
			</small>
		</div>
		<div class="author js-author" data-role-name="{$ROW['role_name']}" data-js="data">
			<i class="far fa-comment"></i>
			<b class="js-user-name" data-js="data">{$ROW['user_name']}</b>
			{assign var=IMAGE value=$ROW['image']}
			{if $IMAGE}
				<img src="{$IMAGE.url}" class="mr-2" alt="{$ROW['user_name']} {$ROW['last_name']}"
					 title="{$ROW['user_name']} {$ROW['last_name']}"
					 height="80" align="left">
				<br/>
			{else}
				<span class="fas fa-user userImage"></span>
			{/if}
		</div>
		<div class="messages">{\App\Purifier::decodeHtml($ROW['messages'])}</div>
	</div>
{/strip}
