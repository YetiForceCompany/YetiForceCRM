{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ConfigEditor-ConfigTemplate -->
	<div>
		<form class="js-form-ajax-submit js-validate-form">
			<input name="module" type="hidden" value="{$MODULE_NAME}" />
			<input name="parent" type="hidden" value="Settings" />
			<input name="action" type="hidden" value="SaveAjax" />
			<input name="type" type="hidden" value="{$CONFIG_NAME}" />
			{assign var="CONFIG_MODEL" value=Settings_ConfigEditor_Module_Model::getInstance()->init($CONFIG_NAME)}
			<div class="p-2">
				{foreach from=$CONFIG_MODEL->getEditFields($CONFIG_NAME) item=FIELD_LABEL key=FIELD_NAME}
					{assign var="FIELD_MODEL" value=$CONFIG_MODEL->getFieldInstanceByName($FIELD_NAME)}
					{if $FIELD_MODEL}
						<div class="form-group row mb-2">
							<label class="col-form-label col-md-4 u-text-small-bold text-left text-lg-right text-md-right">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
								{if $FIELD_MODEL->get('tooltip')}
									<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								{/if}:
							</label>
							<div class="col-md-8 fieldValue m-auto">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE_NAME=$QUALIFIED_MODULE MODULE=$QUALIFIED_MODULE RECORD=null}
							</div>
						</div>
					{/if}
				{/foreach}
			</div>
			<div class="c-form__action-panel">
				<button class="btn btn-success js-save" type="submit">
					<span class="fas fa-check mr-2"></span>
					{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-ConfigEditor-ConfigTemplate -->
{/strip}
