{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Item js-chat-item c-chat__item chatItem {if \App\User::getCurrentUserId() == $ROW['userid']}active {/if} my-3 d-flex align-items-center"
		 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
		{assign var=IMAGE value=$ROW['image']}
		{assign var=IS_IMAGE value=isset($IMAGE['url'])}
		<div class="c-chat__author js-author {if !$IS_IMAGE} col-3 {else}  text-center {/if}"
			 data-role-name="{$ROW['role_name']}" data-js="data">
			<div class="{if !$IS_IMAGE} hide{/if}">
				<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}" class="{if !$IS_IMAGE} hide{/if}"
					 alt="{$ROW['user_name']}"
					 title="{$ROW['user_name']}"/>
			</div>
			<span class="fas fa-user userImage{if $IS_IMAGE} hide{/if}"></span>
			<b class="js-user-name mx-2 {if $IS_IMAGE} hide {/if}" data-js="data">{$ROW['user_name']}</b>
			<div class="">
				<small>
					{$ROW['created']}
				</small>
			</div>
		</div>
		{assign var=USER_COLOR value=\App\Colors::getAllUserColor()}
		<div class="u-w-50px">
			<div class="c-chat__triangle float-right"
				 {if $USER_COLOR[0]['id'] == $ROW['userid']}style="border-right: 10px solid {$USER_COLOR[0]['color']};"{/if}></div>
		</div>
		<div class="messages col-9 p-3"
			 {if $USER_COLOR[0]['id']  == $ROW['userid']}style="background: {$USER_COLOR[0]['color']};"{/if}>
			{\App\Purifier::decodeHtml($ROW['messages'])}
		</div>
	</div>
{/strip}
