{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Mail-TabContent -->
	<form class="js-form-ajax-submit js-validate-form">
		<input name="module" type="hidden" value="{$MODULE_NAME}" />
		<input name="parent" type="hidden" value="Settings" />
		<input name="action" type="hidden" value="SaveAjax" />
		<input name="type" type="hidden" value="{$CONFIG_NAME}" />
		{assign var=MODULE_MODEL value=Settings_Mail_Config_Model::getInstance($CONFIG_NAME)}
		{assign var=STRUCTURE value=$MODULE_MODEL->getFields(true)}

		{* {assign var=CURRENT_BLOCK value=''} *}
		{* {assign var=BLOCK value=$FIELD_MODEL->get('blockLabel')} *}
		{* {assign var=ADD_BLOCK value=$BLOCK && $CURRENT_BLOCK !== $BLOCK} *}
		{foreach from=$STRUCTURE item=FIELDS key=BLOCK name=structre}
			{if $BLOCK}
				{assign var=CURRENT_BLOCK value=$BLOCK}
				<div class="js-toggle-panel c-panel" data-js="click">
					<div class="js-block-header c-panel__header py-2">
						<span class="iconToggle fas fa-chevron-down fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
						<h5>
							<span class="mr-2" aria-hidden="true"></span>
							{\App\Language::translate($CURRENT_BLOCK, $QUALIFIED_MODULE)}
						</h5>
					</div>
					<div class="c-panel__body p-2 js-block-content">
					{/if}
					<div class="form-group row mb-0">
						{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME name=field}
							{assign var=CLASS value=$FIELD_MODEL->getParam('container_class')}
							{if $FIELD_MODEL->getParam('variablePanel')}
								<div class="form-row js-container-variable bc-gray-lighter p-2 m-2 w-100" data-js="container">
									{include file=\App\Layout::getTemplatePath('VariablePanel.tpl') SELECTED_MODULE='Users' PARSER_TYPE='mail'}
								</div>
							{/if}
							<div class="{if $CLASS}{$CLASS}{else}col-md-12{/if} mb-2 js-field-container">
								{if $FIELD_MODEL->getFieldLabel()}
									<label class="u-text-small-bold mb-1">
										{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
										{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
										{if $FIELD_MODEL->get('tooltip')}
											<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
												<span class="fas fa-info-circle"></span>
											</div>
										{/if}:
									</label>
								{/if}
								<div class="fieldValue{if $FIELD_MODEL->getFieldDataType() eq 'boolean'} ml-2 align-top d-inline-block{else} m-auto{/if} {if $FIELD_NAME eq 'email_exceptions'}col-12{/if}">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
								</div>
							</div>
						{/foreach}
					</div>
					{if $BLOCK}
					</div>
				</div>
			{/if}
		{/foreach}

		<div class="c-form__action-panel">
			<button class="btn btn-success js-save" type="submit">
				<span class="fas fa-check mr-2"></span>
				{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
			</button>
		</div>
	</form>
	<!-- /tpl-Settings-Mail-TabContent -->
{/strip}
