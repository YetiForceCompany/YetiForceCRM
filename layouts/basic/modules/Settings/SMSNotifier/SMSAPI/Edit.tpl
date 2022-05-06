{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-SMSNotifier-SMSAPI-Edit -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="validateForm">
			<input type="hidden" name="module" value="{$RECORD_MODEL->getModule()->getName()}" />
			<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
			<input type="hidden" name="providertype" value="{$PROVIDER->getName()}" />
			<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
			{foreach from=$RECORD_MODEL->getEditFields() item=FIELD_MODEL key=FIELD_NAME name=fields}
				<div class="form-group form-row">
					<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
					</label>
					<div class="col-md-8 fieldValue">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=false}
					</div>
				</div>
			{/foreach}
			<hr class="widgetHr" />
			{assign var=SERVICE_USERS value=$RECORD_MODEL->getServiveUsers()}
			<div class="text-center font-weight-bold mb-2">{\App\Language::translate('LBL_CALLBACK_ADDRESSES', $QUALIFIED_MODULE)} <span class="js-popover-tooltip ml-1" data-toggle="popover"
					data-placement="top"
					data-content="{\App\Language::translate('LBL_CALLBACK_ADDRESSES_DESC', $QUALIFIED_MODULE)}" data-js="popover">
					<span class="fas fa-info-circle"></span>
				</span></div>
			<div class="form-group form-row">
				<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
					{\App\Language::translate('FL_CALLBACK_URL_FOR_REPORT', $QUALIFIED_MODULE)}
				</label>
				<div class="col-md-8 input-group">
					<select id="callback_url" tabindex="0" title="{\App\Language::translate('FL_PROVIDER', $QUALIFIED_MODULE)}" class="select2 form-control">
						{foreach from=$SERVICE_USERS item=SERVICE_USER}
							<option value="{$PROVIDER->getCallBackUrlByService($SERVICE_USER, 'Report')}">{\App\Purifier::encodeHtml(\App\Fields\Owner::getUserLabel((int) $SERVICE_USER['user_id']))}</option>
						{/foreach}
					</select>
					<span class="input-group-append">
						<button class="btn btn-outline-secondary clipboard js-popover-tooltip" data-copy-target='#callback_url' type="button" data-placement="top" data-content="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}">
							<span class="fas fa-copy"></span>
						</button>
					</span>
					<span class="input-group-append">
						<button class="btn btn-outline-secondary js-popover-tooltip" type="button" data-placement="top" data-content="{\App\Language::translate('FL_CALLBACK_URL_FOR_REPORT_DESC', $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</button>
					</span>
				</div>
			</div>
			<div class="form-group form-row">
				<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
					{\App\Language::translate('FL_CALLBACK_URL_FOR_REPLY', $QUALIFIED_MODULE)}
				</label>
				<div class="col-md-8 input-group">
					<select id="callback_url_reply" tabindex="0" title="{\App\Language::translate('FL_PROVIDER', $QUALIFIED_MODULE)}" class="select2 form-control">
						{foreach from=$SERVICE_USERS item=SERVICE_USER}
							<option value="{$PROVIDER->getCallBackUrlByService($SERVICE_USER, 'Reception')}">{\App\Purifier::encodeHtml(\App\Fields\Owner::getUserLabel((int) $SERVICE_USER['user_id']))}</option>
						{/foreach}
					</select>
					<span class="input-group-append">
						<button class="btn btn-outline-secondary clipboard js-popover-tooltip" data-copy-target='#callback_url_reply' type="button" data-placement="top" data-content="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}">
							<span class="fas fa-copy"></span>
						</button>
					</span>
					<span class="input-group-append">
						<button class="btn btn-outline-secondary js-popover-tooltip" type="button" data-placement="top" data-content="{\App\Language::translate('FL_CALLBACK_URL_FOR_REPLY_DESC', $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</button>
					</span>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-SMSNotifier-SMSAPI-Edit -->
{/strip}
