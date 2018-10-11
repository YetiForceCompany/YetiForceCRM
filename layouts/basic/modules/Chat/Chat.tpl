{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Chat -->
	{function ROOM_ITEM CLASS_NAME=''}
		<a href="#"
		   class="js-change-room{if \App\Chat::getCurrentRoomId()==$ROOM['room_id'] } fontBold{/if} {$CLASS_NAME}"
		   data-room-id="{$ROOM['room_id']}" data-js="click">
			<div class="row">
				{\App\Language::translate($ROOM['name'])}
			</div>
		</a>
	{/function}
	<div class="o-action-menu__item">
		<a class="c-header__btn ml-2 btn btn-light btn headerLinkChat js-popover-tooltip"
		   role="button"
		   data-js="popover" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
		<span class="fas fa-comments fa-fw"
			  title="{\App\Language::translate('LBL_CHAT')}"></span>
			<span class="c-header__label--sm-down"> {\App\Language::translate('LBL_CHAT')}</span>

		</a>
		<div class="chatModal o-chat-modal-window modal-full fade c-modal--custom-animation js-chat-modal" tabindex="-1"
			 role="dialog"
			 aria-labelledby="c-chat-modal__title"
			 data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}"
			 data-chat-room-id="{\App\Chat::getCurrentRoomId()}">
			<div class="modal-dialog">
				<div class="modal-body modalRightSiteBar px-0" role="document">
					<div class="modal-content rounded-0 js-chat-container">
						<div class="modal-header">
							<h5 class="modal-title" id="c-chat-modal__title">
								<span class="fas fa-comments fa-fw mr-1"></span>
								{\App\Language::translate('LBL_CHAT')}
							</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="row">
							<div class="col-sm-2 pl-5">
								{ROOM_ITEM ROOM=['room_id'=>'', 'name'=>''] CLASS_NAME='js-room-template hide'}
								<div class="js-chat-rooms-list">
									{foreach item=ROOM from=\App\Chat::getRoomsByUser()}
										{ROOM_ITEM ROOM=$ROOM}
									{/foreach}
								</div>
							</div>
							<div class="col-sm-10 ps pr-4">
								<div class="js-chat-items js-chat-room-{\App\Chat::getCurrentRoomId()} o-chat-items"
									 data-js="html">
									{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat') CHAT_ENTRIES=$CHAT->getEntries()}
								</div>
								{include file=\App\Layout::getTemplatePath('Detail/ChatFooter.tpl')}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Chat -->
{/strip}
