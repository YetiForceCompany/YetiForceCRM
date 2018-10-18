{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Chat-Modal -->
	{function ROOM_ITEM CLASS_NAME=''}
		{assign var=SELECTED value=false}
		<li>{$ROOM['name']}</li>
	{/function}
	<div class="modal-body pt-0 pb-0">
		<div class="row p-0">
			<div class="col-2 bg-color-grey-50 m-0 p-0">
				<div class="text-uppercase bg-color-grey-200 p-2">{\App\Language::translate('LBL_ROOM_FAVORITE', $MODULE_NAME)}</div>
				<ul>
					{foreach item=ROOM from=\App\Chat::getRoomsByUser()}
						{ROOM_ITEM ROOM=$ROOM CLASS_NAME='d-flex'}
					{/foreach}
				</ul>
				<div class="text-uppercase bg-color-grey-200 p-2">{\App\Language::translate('LBL_ROOM_GROUP', $MODULE_NAME)}</div>
				<ul>
					{foreach item=ROOM from=\App\Chat::getRoomsByUser()}
						{ROOM_ITEM ROOM=$ROOM CLASS_NAME='d-flex'}
					{/foreach}
				</ul>
				<div class="text-uppercase bg-color-grey-200 p-2">{\App\Language::translate('LBL_ROOM_GLOBAL', $MODULE_NAME)}</div>
				<ul>
					{foreach item=ROOM from=\App\Chat::getRoomsByUser()}
						{ROOM_ITEM ROOM=$ROOM CLASS_NAME='d-flex'}
					{/foreach}
				</ul>
			</div>
			<div class="col-10 m-0">
				{include file=\App\Layout::getTemplatePath('Chat.tpl', 'Chat')}
			</div>
		</div>
	</div>
	<!-- /tpl-Chat-Modal -->
{/strip}
