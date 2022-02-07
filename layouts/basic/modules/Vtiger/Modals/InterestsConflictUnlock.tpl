{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-InterestsConflictUnlock -->
	<div class="modal-body mb-0">
		{if empty($BASE_RECORD)}
			<div class="alert alert-warning mb-0" role="alert">
				<h4 class="alert-heading mb-1">
					<span class="fas fa-exclamation-triangle pr-3"></span>
					{\App\Language::translate('LBL_RELATION_NOT_FOUND')}
				</h4>
			</div>
		{else}
			<form class="form-horizontal js-modal-form js-validate-form" data-js="container|validate">
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="sourceModuleName" value="{$MODULE_NAME}" />
				<input type="hidden" name="baseRecord" value="{$BASE_RECORD}" />
				<input type="hidden" name="baseModuleName" value="{$BASE_MODULE_NAME}" />
				<p>
					{\App\Language::translate('LBL_ACCESS_REQUEST_TO')}: {\App\Record::getLabel($BASE_RECORD)}
				</p>
				<textarea name="comment" class="form-control" data-validation-engine="validate[required,maxSize[255]]" placeholder="{\App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_DESC')}"></textarea>
				<button type="button" class="btn btn-success mt-2 float-right js-ic-send-btn" data-js="click">
					<span class="fas fa-paper-plane mr-2"></span>
					{\App\Language::translate('LBL_SEND')}
				</button>
			</form>
		{/if}
	</div>
	<!-- /tpl-Base-Modals-InterestsConflictUnlock -->
{/strip}
