{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-MultiDependField -->
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var=FIELDS_MODEL value=$FIELD_MODEL->getUITypeModel()->getFieldsModel()}
	<div class="d-flex align-items-center js-multi-field" data-js="container">
		<input name="{$FIELD_MODEL->getFieldName()}" value="{if $FIELD_MODEL->get('fieldvalue')}{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}{/if}"
			type="hidden" class="js-multi-field-val" data-js="value" data-fields="{\App\Purifier::encodeHtml(\App\Json::encode(array_keys($FIELDS_MODEL)))}" />
		<button type="button" class="btn btn-outline-success border mr-2 mb-2 h-100 js-multi-field-add-item" tabindex="{$FIELD_MODEL->getTabIndex()}" data-js="click">
			<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD', $MODULE_NAME)}"></span>
		</button>
		<div class="form-inline w-100">
			{foreach item=VALUE from=$FIELD_VALUE}
				<div class="form-group mr-1 mb-2 js-multi-field-row w-100" data-js="container|clone">
					<div class="input-group {$WIDTHTYPE_GROUP} w-100">
						<div class="input-group-prepend">
							<button type="button" class="btn btn-outline-danger border js-remove-item" data-js="click">
								<span class="fas fa-times" title="{\App\Language::translate('LBL_REMOVE', $MODULE_NAME)}"></span>
							</button>
						</div>
						{foreach item=FIELD_MODEL_PART key=FIELD_NAME from=$FIELDS_MODEL}
							<div class="u-w-xsm-40">
								{if $VALUE && isset($VALUE[$FIELD_NAME])}
									{assign var=FIELD_MODEL_PART value=$FIELD_MODEL_PART->set('fieldvalue', $VALUE[$FIELD_NAME])}
								{/if}
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL_PART->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL_PART}
							</div>
						{/foreach}
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Base-Edit-Field-MultiDependField -->
{/strip}
