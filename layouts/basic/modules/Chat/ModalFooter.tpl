{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-ModalFooter modal-footer bg-color-grey-200 js-chat-footer p-1"
		 data-lbl-history="{\App\Language::translate('LBL_HISTORY_CHAT', $MODULE_NAME)}"
		 data-lbl-unread="{\App\Language::translate('LBL_UNREAD', $MODULE_NAME)}"
		 data-js="container">
		{assign var=ROOM_TYPE value=$CURRENT_ROOM['roomType']}
		{assign var=ROOMS_USER value=\App\Chat::getRoomsByUser()}
		{assign var=LBL_GROUP_ROOM value="LBL_ROOM_$ROOM_TYPE"|upper}
		<div class="float-left col-8 text-uppercase js-footer-history" data-js="class .js-footer-history">
			<ol class="breadcrumb m-0 p-0">
				<li class="breadcrumb-item">
					<span class="js-footer-group-name"
						  data-js="container">{\App\Language::translate($LBL_GROUP_ROOM, $MODULE_NAME)}</span>
				</li>
				<li class="breadcrumb-item active">
					<span class="js-footer-room-name o-chat-footer" data-js="container">
						{if isset($ROOMS_USER[$ROOM_TYPE][0]['name'])}{$ROOMS_USER[$ROOM_TYPE][0]['name']}{/if}
					</span>
				</li>
			</ol>
		</div>
		<div class="float-left col-8 hide text-uppercase js-footer-history-hide"
			 data-js="class .js-footer-history-hide">
			<ol class="breadcrumb m-0 p-0">
				<li class="breadcrumb-item active">
					<span class="o-chat-footer js-breadcrumb-item">{\App\Language::translate('LBL_HISTORY_CHAT', $MODULE_NAME)}</span>
				</li>
			</ol>
		</div>
		<div class="float-right col-4">
		</div>
	</div>
	</div><!-- Close DIV modal-content -->
	</div><!-- Close DIV modal-dialog -->
	</div><!-- Close DIV modal -->
{/strip}
