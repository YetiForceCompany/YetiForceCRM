{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-SMSNotifier-MassSMS -->
	<form class="form-horizontal js-validate-form" id="massSave" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="action" value="MassSMS" />
		<input type="hidden" name="viewname" value="{$VIEWNAME}" />
		<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}">
		<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}">
		<input type="hidden" name="search_key" value="{$SEARCH_KEY}" />
		<input type="hidden" name="entityState" value="{$ENTITY_STATE}" />
		<input type="hidden" name="operator" value="{$OPERATOR}" />
		<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
		<input type="hidden" name="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
		<input type="hidden" name="advancedConditions" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_CONDITIONS))}" />
		<div class="modal-body">
			<div class="alert alert-info" role="alert">
				<button type="button" class="close px-2 pb-1" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<span class="fas fa-info-circle"></span>&nbsp;&nbsp;
				{\App\Language::translate('LBL_MASS_SEND_SMS_INFO', $MODULE_NAME)}<br>
				<span class="fas fa-info-circle"></span>&nbsp;&nbsp;
				{\App\Language::translate('LBL_LIMIT_OF_ONETIME_CREATED_TEXT_MESSAGES', $MODULE_NAME)}: {$SMS_LIMIT}
			</div>
			<div class="form-group row mb-0">
				<div class="col-12 col-md-6 mb-2">
					<label class="u-text-small-bold mb-1">{\App\Language::translate('LBL_SELECT_THE_PHONE_NUMBER_FIELDS_TO_SEND', $MODULE_NAME)}:</label>
					<div class="fieldValue m-auto">
						<select name="fields[]"
							data-placeholder="{\App\Language::translate('LBL_ADD_MORE_FIELDS',$MODULE_NAME)}"
							multiple="multiple" class="select2 form-control"
							data-validation-engine="validate[required]">
							<optgroup>
								{foreach item=PHONE_FIELD from=$PHONE_FIELDS}
									<option value="{$PHONE_FIELD->getName()}">
										{if !empty($SINGLE_RECORD)}
											{assign var=FIELD_VALUE value=$SINGLE_RECORD->getDisplayValue($PHONE_FIELD->getName(), false, true)}
										{/if}
										{\App\Language::translate($PHONE_FIELD->getFieldLabel(), $PHONE_FIELD->getModuleName())}
										{if !empty($FIELD_VALUE)}&nbsp;({$FIELD_VALUE}){/if}
									</option>
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
				{if $TEMPLATES}
					<div class="col-12 col-md-6 mb-2">
						<label class="u-text-small-bold mb-1">{\App\Language::translate('LBL_TEMPLATE', $MODULE_NAME)}:</label>
						<div class="fieldValue m-auto">
							<select class="select2 form-control" id="template" data-select="allowClear">
								<optgroup class="p-0">
									<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
								</optgroup>
								{foreach item=TEMPLATE from=$TEMPLATES}
									<option value="{\App\Purifier::encodeHtml($TEMPLATE.message)}">{\App\Purifier::encodeHtml($TEMPLATE.name)}
									</option>
								{/foreach}
							</select>
						</div>
					</div>
				{/if}
				{if $FIELD_IMAGE && $FIELD_IMAGE->isViewable()}
					<div class="col-12 col-md-6 mb-2">
						<label class="u-text-small-bold mb-1">{\App\Language::translate('LBL_ADD_IMAGE_FILE', $MODULE_NAME)}:</label>
						<div class="fieldValue m-auto">
							{include file=\App\Layout::getTemplatePath($FIELD_IMAGE->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_IMAGE MODULE=$MODULE_NAME RECORD=false}
						</div>
					</div>
				{/if}
			</div>
			<div class="form-group">
				<label class="u-text-small-bold mb-1">{\App\Language::translate('LBL_TYPE_THE_MESSAGE', $MODULE_NAME)}:</label>
				<div class="fieldValue">
					{include file=\App\Layout::getTemplatePath($FIELD_MESSAGE->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MESSAGE MODULE=$MODULE_NAME RECORD=false}
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-SMSNotifier-MassSMS -->
{/strip}
