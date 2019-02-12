{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-DefaultDate -->
	{assign var=IS_CUSTOM_DEFAULT_VALUE value=\App\TextParser::isVaribleToParse($FIELD_MODEL->get('defaultvalue'))}
	<div class="js-base-element row {if $IS_CUSTOM_DEFAULT_VALUE} d-none{/if}" data-js="container|data-name"
		 data-name="{$FIELD_MODEL->getName()}">
		<div class="col-10">
			{assign var="FIELD_MODEL" value=$FIELD_MODEL->set('fieldvalue',$FIELD_MODEL->get('defaultvalue'))}
			{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $FIELD_MODEL->getModuleName())
			MODULE=$FIELD_MODEL->getModuleName() RECORD=null}
		</div>
		<div class="col-2">
			<span class="input-group-prepend"
				  title="{\App\Purifier::encodeHtml(App\Language::translate('LBL_CUSTOM_CONFIGURATION', $QUALIFIED_MODULE))}">
				<button type="button" class="btn btn-light configButton btn-outline-secondary">
					<span class="fas fa-cog"></span>
				</button>
			</span>
		</div>
	</div>
	<div class="input-group js-base-element {if !$IS_CUSTOM_DEFAULT_VALUE} d-none{/if}" data-js="container|data-name"
		 data-name="{$FIELD_MODEL->getName()}">
		<input name="{$FIELD_MODEL->getName()}"
			   value="{if $IS_CUSTOM_DEFAULT_VALUE}{$FIELD_MODEL->get('defaultvalue')}{/if}"
			   type="text"
			   class="form-control" {if !$FIELD_MODEL->hasDefaultValue() || !$IS_CUSTOM_DEFAULT_VALUE} disabled="disabled"{/if}
			   data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			   data-fieldinfo="{\App\Purifier::encodeHtml('{"type":"textParser"}')}"/>
		<span class="input-group-prepend">
			<button class="btn btn-light varibleToParsers" type="button">
				<span class="fas fa-edit"></span>
			</button>
			<button class="btn btn-light active configButton" type="button"
					title="{\App\Purifier::encodeHtml(App\Language::translate('LBL_CUSTOM_CONFIGURATION', $QUALIFIED_MODULE))}">
				<span class="fas fa-cog"></span>
			</button>
		</span>
	</div>
{/strip}
