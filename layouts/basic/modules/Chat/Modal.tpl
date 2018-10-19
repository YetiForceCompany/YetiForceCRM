{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Modal -->
	{function ROOM_ITEM CLASS_NAME=''}
		{assign var=SELECTED value=$CURRENT_ROOM['roomId']==$ROOM['roomid'] && $CURRENT_ROOM['roomType']==$ROOM_TYPE }
		<li class="text-truncate js-room {if $SELECTED} active{/if}"
			title="{\App\Purifier::encodeHtml(\App\Language::translate($ROOM['name'], 'Chat'))}"
			data-room-id="{$ROOM['roomid']}"
			data-js="click">
			{\App\Language::translate($ROOM['name'], 'Chat')}
		</li>
	{/function}
	<div class="modal-body pt-0 pb-0">
		<div class="row p-0">
			<div class="col-2 bg-color-grey-50 m-0 p-0 js-room-list" data-js="container">
				{foreach item=GROUP_ROOM key=KEY from=\App\Chat::getRoomsByUser()}
					{assign var=LBL_GROUP_ROOM value="LBL_ROOM_$KEY"|upper}
					<div class="text-uppercase bg-color-grey-200 p-2">
						{\App\Language::translate($LBL_GROUP_ROOM, $MODULE_NAME)}
					</div>
					<ul class="js-room-type" data-room-type="{$KEY}" data-js="data">
						{foreach item=ROOM from=$GROUP_ROOM}
							{ROOM_ITEM ROOM=$ROOM CLASS_NAME='' ROOM_TYPE=$KEY }
						{/foreach}
					</ul>
				{/foreach}
			</div>
			<div class="col-10 m-0">
				{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Modal -->
{/strip}
