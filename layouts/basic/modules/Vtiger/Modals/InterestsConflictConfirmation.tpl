{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-InterestsConflictConfirmation -->
	<div class="modal-body mb-0 text-center">
		{if empty($BASE_RECORD)}
			<div class="alert alert-warning mb-0" role="alert">
				<h4 class="alert-heading mb-1">
					<span class="fas fa-exclamation-triangle pr-3"></span>
					{\App\Language::translate('LBL_RELATION_NOT_FOUND')}
				</h4>
			</div>
		{else}
			<form class="form-horizontal js-modal-form" data-js="container">
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="sourceModuleName" value="{$MODULE_NAME}" />
				<input type="hidden" name="baseRecord" value="{$BASE_RECORD}" />
				<input type="hidden" name="baseModuleName" value="{$BASE_MODULE_NAME}" />
				<h3>
					{\App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRMATION_DESC')}:<br>
					{\App\Record::getHtmlLink($BASE_RECORD,$BASE_MODULE_NAME, \App\Config::main('href_max_length'))}
				</h3>
				<button type="button" class="btn btn-danger btn-lg mr-5 mt-3 js-ic-confirmation" data-value="{\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_YES}" data-js="click">
					<span class="fas fa-check mr-1"></span> {\App\Language::translate('LBL_YES')}
				</button>
				<button type="button" class="btn btn-success btn-lg mt-3 js-ic-confirmation" data-value="{\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_NO}" data-js="click">
					<span class="fas fa-times mr-1"></span> {\App\Language::translate('LBL_NO')}
				</button>
			</form>
		{/if}
	</div>
	<!-- tpl-Base-Modals-InterestsConflictConfirmation -->
{/strip}
