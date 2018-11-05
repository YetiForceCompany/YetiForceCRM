{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-History">
		{foreach item=HISTORY key=$KEY_ITEM from=$HISTORY_GROUP}
			<div class="my-3">
				<div class="m-0 w-100 row">
					<div class="m-0 u-w-79px">
					</div>
					<div class="u-font-size-10px m-0 text-truncate text-left col-9 px-0">
						{$HISTORY['created']}
					</div>
				</div>
				<div class="d-flex py-0 align-items-center">
					<div class="p-1 o-chat__img-container">
						<img src="{if $IS_IMAGE}{$IMAGE['url']}{/if}"
							 class="{if !$IS_IMAGE} hide{/if} o-chat__author-img"
							 alt="{$ROW['user_name']}"
							 title="{$ROW['user_name']}"/>
						<span class="fas fa-user u-font-size-38px {if $IS_IMAGE} hide{/if} o-chat__author-name"
							  title="{$ROW['user_name']}"></span>
					</div>
					<div class="flex-grow-1 ml-1 p-1">
						{assign var=ROW value=$CHAT_ENTRIES[0]}
						<div class="o-chat__item d-flex align-items-center"
							 data-mid="{$ROW['id']}" data-user-id="{$ROW['userid']}" data-js="data">
							{assign var=IMAGE value=$ROW['image']}
							{assign var=IS_IMAGE value=isset($IMAGE['url'])}

							<div class="o-chat__triangle ownerCT_{$ROW['userid']} active float-right u-border-right-10px "></div>
							<div class="col-12  px-0">
								<div class="o-chat__messages col-12 p-3  ownerCBg_{$ROW['userid']}  float-left ">{\App\Purifier::decodeHtml($HISTORY['messages'])}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}