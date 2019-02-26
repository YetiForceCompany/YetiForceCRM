{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Item -->
	{assign var=USER_ID value=$USER_MODEL->getId()}
	<div class="js-chat-item o-chat__item chatItem {if $USER_ID == $ROW['userid']} active flex-row {else} flex-row-reverse {/if} my-3 d-flex align-items-center"
		 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
		{assign var=IS_IMAGE value=!empty($ROW['image'])}
		<div class="u-w-50px {if $USER_ID == $ROW['userid']} mr-3 {else} ml-3 {/if} js-author text-center"
			 data-user-name="{$ROW['user_name']}"
			 data-role-name="{$ROW['role_name']}" data-js="data">
			<div class="js-image o-chat__img-container mx-auto {if !$IS_IMAGE} p-1 {/if}" data-js="copy">
				<img src="{$ROW['image']}" class="{if !$IS_IMAGE} hide{/if} js-chat-image_src o-chat__author-img"
					 alt="{$ROW['user_name']}"
					 title="{$ROW['user_name']}" data-js="hide"/>
				<span class="js-chat-image_icon fas fa-user u-font-size-38px {if $IS_IMAGE} hide{/if} o-chat__author-name"
					  title="{$ROW['user_name']}" data-js="hide"></span>
			</div>
			<span class="u-font-size-10px m-0 text-truncate text-secondary">
				{if !empty($ROW['user_name'])}{\App\Utils::getInitials($ROW['user_name'])}{/if}
			</span>
		</div>
		<div class="o-chat__triangle ownerCT_{$ROW['userid']} {if $USER_ID == $ROW['userid']} active float-right u-border-right-10px  {else} float-left u-border-left-10px  {/if}"></div>
		<div class="col-9  px-0">
			<div class="o-chat__name u-font-size-10px m-0 w-100 ownerCBg_{$ROW['userid']} row">
				<span class="u-font-size-10px m-0 text-truncate text-left col-6 p-1">
					{$ROW['user_name']}
				</span>
				<span class="u-font-size-10px m-0 text-truncate text-right col-6 p-1">
					{$ROW['created']}
				</span>
			</div>
			<div class="o-chat__messages js-message col-12 p-3 ownerCBg_{$ROW['userid']} {if $USER_ID == $ROW['userid']} active float-right  {else} float-left {/if}"
				 data-js="data">{nl2br(\App\Utils\Completions::decode(\App\Purifier::purifyHtml(\App\Purifier::decodeHtml($ROW['messages']))))}</div>
		</div>
	</div>
	<!-- /tpl-Chat-Item -->
{/strip}
