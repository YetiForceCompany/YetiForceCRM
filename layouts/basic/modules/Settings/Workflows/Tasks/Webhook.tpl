{strip}
	{*
<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<!-- tpl-Settings-Workflows-Tasks-Webhook -->
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_WEBHOOK_URL', $QUALIFIED_MODULE)}<span class="redColor">*</span></span>
		<div class="col-md-4">
			<input data-validation-engine='validate[required, funcCall[Vtiger_Url_Validator_Js.invokeValidation]]' class="form-control" name="url" type="text"
				value="{if isset($TASK_OBJECT->url)}{$TASK_OBJECT->url}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_HTTP_AUTH_LOGIN', $QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<input data-validation-engine='validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]' class="form-control" name="login" type="text" autocomplete="off" value="{if isset($TASK_OBJECT->login)}{$TASK_OBJECT->login}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_HTTP_AUTH_PASSWORD', $QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<input class="form-control" data-validation-engine='validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]' name="password" type="password"
				value="{if isset($TASK_OBJECT->password)}{$TASK_OBJECT->password}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_FORMAT', $QUALIFIED_MODULE)}<span class="redColor">*</span></span>
		<div class="col-md-4">
			<select data-validation-engine='validate[required]' class="select2 form-control" name="format" data-placeholder="{\App\Language::translate('LBL_FORMAT')}">
				{foreach item=FORMAT from=['json', 'form_params']}
					<option value="{$FORMAT}" data-label="{$FORM}" {if isset($TASK_OBJECT->format) && $TASK_OBJECT->format eq $FORMAT}selected="" {/if}>
						{$FORMAT}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<select name="fields[]" class="select2" data-select="allowClear" multiple="multiple">
				{foreach from=$MODULE_MODEL->getFields() item=FIELD_MODEL}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
					<option value="{$FIELD_MODEL->getName()}" {if isset($TASK_OBJECT->fields) && in_array($FIELD_MODEL->getName(), $TASK_OBJECT->fields)} selected="" {/if}>
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_DATA_TYPE', $QUALIFIED_MODULE)}<span class="redColor">*</span></span>
		<div class="col-md-4">
			<select data-validation-engine='validate[required]' class="select2 form-control" name="typedata[]"
				data-placeholder="{\App\Language::translate('LBL_DATA_TYPE', $QUALIFIED_MODULE)}" multiple="multiple">
				{assign var=DATA_TYPES value=['data' => 'LBL_DATA_FORMAT_USER', 'rawData' => 'LBL_DATA_FORMAT_DATABASE', 'changes' => 'LBL_DATA_CHANGED']}
				{foreach item=DATA_LABEL key=DATA_TYPE from=$DATA_TYPES}
					<option value="{$DATA_TYPE}" {if isset($TASK_OBJECT->typedata) && in_array($DATA_TYPE, $TASK_OBJECT->typedata)} selected="" {/if}>
						{\App\Language::translate($DATA_LABEL, $QUALIFIED_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<!-- /tpl-Settings-Workflows-Tasks-VTWebhook -->
{/strip}
