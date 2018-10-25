{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=USER value=\App\User::getCurrentUserId()}
	<div class="tpl-Chat-Item js-chat-item c-chat__item chatItem {if $USER == $ROW['userid']} active flex-row {else} flex-row-reverse {/if} my-1 d-flex align-items-center"
		 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
		{assign var=IMAGE value=$ROW['image']}
		{assign var=IS_IMAGE value=isset($IMAGE['url'])}
		<div class="c-chat__author js-author  text-center u-w-100px"
			 data-role-name="{$ROW['role_name']}" data-js="data">
			<div class="p-1 chat_img-container mx-auto">
				<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}" class="{if !$IS_IMAGE} hide{/if}"
					 alt="{$ROW['user_name']}"
					 title="{$ROW['user_name']}"/>
				<span class="fas fa-user u-font-size-50px {if $IS_IMAGE} hide{/if}" title="{$ROW['user_name']}"></span>
			</div>
			<span class="u-font-size-10px m-0 text-truncate text-secondary">
				{$ROW['created']}
			</span>
		</div>
		<div class="u-w-50px">
			<div class="c-chat__triangle {if $USER == $ROW['userid']} active float-right u-border-right-10px ownerCT_{$ROW['userid']} {else} float-left u-border-left-10px  ownerCT_{$ROW['userid']} {/if}"></div>
		</div>
		<div class="messages col-9 p-3 {if $USER == $ROW['userid']} active float-right ownerCBg_{$ROW['userid']}  {else} float-left ownerCBg_{$ROW['userid']}  {/if}">{\App\Purifier::decodeHtml($ROW['messages'])}</div>
	</div>
{/strip}
