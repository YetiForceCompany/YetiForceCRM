{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Detail-ChatInput row mx-0 mb-1 mt-1 d-block">
		{function INPUT_MESSAGE}
			<input type="text" class="form-control message js-chat-message"{' '}
				   placeholder="{\App\Language::translate('LBL_MESSAGE', 'Notification')}" autocomplete="off"{' '}
				   data-js="keydown"/>
		{/function}
		{if isset($BTN_FAVORITE) && $BTN_FAVORITE}
			<div class="row">
				<div class="col-11">
					{INPUT_MESSAGE}
				</div>
				<div class="col-1">
					{if $CHAT->isFavorite()}
						<button type="button" class="btn btn-danger js-chat-favorite" data-favorite="false"
								data-js="click">
							<span class="fas fa-star color-yellow-600"></span>
						</button>
					{else}
						<button type="button" class="btn btn-success js-chat-favorite" data-favorite="true"
								data-js="click">
							<span class="fas fa-star color-yellow-600"></span>
						</button>
					{/if}
				</div>
			</div>
		{else}
			{INPUT_MESSAGE}
		{/if}
	</div>
{/strip}
