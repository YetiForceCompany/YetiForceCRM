{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-ChatInput row mx-0 mb-1 mt-1 d-block">
		{function INPUT_MESSAGE}
			<input type="text" class="form-control message js-chat-message"{' '}
				   placeholder="{\App\Language::translate('LBL_MESSAGE')}" autocomplete="off"{' '}
				   data-js="keydown"/>
		{/function}
		{function BUTTON_FAVORITE FAVORITE="false" LBL_REMOVE='LBL_REMOVE_FROM_FAVORITES' LBL_ADD='LBL_ADD_TO_FAVORITES'}
			<button type="button" class="btn {$BTN_CLASS} js-chat-favorite" data-favorite="{$FAVORITE}" data-js="click"
					data-label-remove="{\App\Language::translate($LBL_REMOVE)}"
					data-label-add="{\App\Language::translate($LBL_ADD)}">
				<span class="fas fa-star color-yellow-600"></span>
				<span class="js-lable" data-js="replace">
					{if $FAVORITE}
						{\App\Language::translate($LBL_ADD)}
					{else}
						{\App\Language::translate($LBL_REMOVE)}
					{/if}
				</span>
			</button>
		{/function}
		{if isset($BTN_FAVORITE) && $BTN_FAVORITE}
			<div class="row">
				<div class="col-10">
					{INPUT_MESSAGE}
				</div>
				<div class="col-2">
					{if $CHAT->isFavorite()}
						{BUTTON_FAVORITE BTN_CLASS='btn-danger' FAVORITE='false'}
					{else}
						{BUTTON_FAVORITE BTN_CLASS='btn-success' FAVORITE='true'}
					{/if}
				</div>
			</div>
		{else}
			{INPUT_MESSAGE}
		{/if}
	</div>
{/strip}
