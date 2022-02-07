{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Modals-SendInvitationModal -->
	<div class="modal-body js-modal-body mb-0 pt-0" data-js="container">
		<form class="form-horizontal validateForm">
			<input type="hidden" name="ics" value="1" />
			<input type="hidden" name="crmModule" value="{$MODULE_NAME}" />
			<input type="hidden" name="crmRecord" value="{$RECORD_ID}" />
			{function SEND_TO LABEL='' NAME=''}
				<div class="form-group form-row mb-0">
					<label class="col-sm-12 col-form-label">{\App\Language::translate($LABEL, $MODULE_NAME)}</label>
					<div class="col-sm-12">
						<select id="{$NAME}{\App\Layout::getUniqueId()}" name="{$NAME}" class="select2 form-control" multiple="multiple"
							data-placeholder="{\App\Language::translate('LBL_SELECT_SOME_OPTIONS', $MODULE_NAME)}">
							<option></option>
							{foreach item=FIELDS key=BLOCK_NAME from=$EMAIL_FIELD_OPTION}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=LABEL key=VAL from=$FIELDS}
										<option value="{$VAL}">{$LABEL}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
			{/function}
			{SEND_TO LABEL='LBL_TO' NAME='_to'}
			{SEND_TO LABEL='LBL_CC' NAME='_cc'}
			<div class="form-group form-row mt-2 mb-0">
				<label class="col-sm-3 col-form-label">{\App\Language::translate('LBL_EMAIL_TEMPLATE')}</label>
				<div class="col-sm-9">
					<select class="select2 form-control" name="template">
						<option value="0">{\App\Language::translate('LBL_SELECT_OPTION', $MODULE_NAME)}</option>
						{foreach item=ROW from=App\Mail::getTemplateList($MODULE_NAME, 'PLL_RECORD')}
							<option value="{$ROW['id']}">{$ROW['name']}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Calendar-Modals-SendInvitationModal -->
{/strip}
