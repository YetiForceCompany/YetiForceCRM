{strip}
{*
<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Settings-Workflows-Tasks-VTWebhook -->
<div id="VtWebhookContainer">
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_ADDRESS_WEBHOOK',
			$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
		<div class="col-md-4">
			<input data-validation-engine='validate[required, funcCall[Vtiger_Url_Validator_Js.invokeValidation]]' class="form-control" name="url" type="text"
				value="{if isset($TASK_OBJECT->url)}{$TASK_OBJECT->url}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_LOGIN')}</span>
		<div class="col-md-4">
			<input data-validation-engine='validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]' class="form-control" name="login" type="text" autocomplete="off"
				value="{if isset($TASK_OBJECT->login)}{$TASK_OBJECT->login}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_PASSWORD',
			$QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<input class="form-control" data-validation-engine='validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]' name="password" type="password"
				value="{if isset($TASK_OBJECT->password)}{$TASK_OBJECT->password}{/if}" />
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SELECT_FIELD',
			$QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<select class="select2 form-control" name="fieldsdata"
				data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD')}" multiple="multiple">
				{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
				{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable()}
					<optgroup label="{$BLOCK_NAME}">
						{foreach item=ITEM from=$FIELDS}
							<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}" {if isset($TASK_OBJECT->
								fieldsdata) && $TASK_OBJECT->fieldsdata &&
								in_array($ITEM['var_value'],$TASK_OBJECT->fieldsdata)}selected=""{/if}>
								{$ITEM['label']}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row pb-3">
		<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_DATA_TYPE',
			$QUALIFIED_MODULE)}</span>
		<div class="col-md-4">
			<select class="select2 form-control" name="typedata"
				data-placeholder="{\App\Language::translate('LBL_DATA_TYPE')}" multiple="multiple">
				{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
				{foreach item=DATA_TYPE from=['LBL_DATA_FORMAT_USER', 'LBL_DATA_FORMAT_DATABASE', 'LBL_DATA_CHANGED']}
					<option value="{$DATA_TYPE}" data-label="{$DATA_TYPE}" {if isset($TASK_OBJECT->typedata) &&
						$TASK_OBJECT->typedata}selected=""{/if}>
						{\App\Language::translate($DATA_TYPE)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
</div>
<!-- /tpl-Settings-Workflows-Tasks-VTWebhook -->
{/strip}
