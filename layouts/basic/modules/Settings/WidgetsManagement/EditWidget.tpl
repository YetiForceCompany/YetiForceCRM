{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WidgetsManagement-EditWidget -->
	<form class="form-horizontal js-send-by-ajax js-validate-form" method="POST">
		<input name="module" type="hidden" value="WidgetsManagement" />
		<input name="parent" type="hidden" value="Settings" />
		<input name="action" type="hidden" value="SaveAjax" />
		<input name="mode" type="hidden" value="save" />
		<input name="linkId" type="hidden" value="{$WIDGET_MODEL->get('linkid')}" />
		<input name="blockId" type="hidden" value="{$WIDGET_MODEL->get('blockid')}" />
		{if $WIDGET_MODEL->getId()}
			<input name="widgetId" type="hidden" value="{$WIDGET_MODEL->getId()}" />
		{/if}
		<div class="modal-body">
			<div class="clearfix">
				{foreach from=$WIDGET_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=fields}
					{assign var="FIELD_MODEL" value=$WIDGET_MODEL->getFieldInstanceByName($FIELD_NAME)}
					{if $FIELD_MODEL}
						<div class="form-group row mb-2">
							<label class="col-form-label col-md-4 u-text-small-bold text-left text-lg-right text-md-right">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
								{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
								{if $FIELD_MODEL->get('tooltip')}
									<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								{/if}:
							</label>
							<div class="col-md-8 fieldValue m-auto">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null}
							</div>
						</div>
					{/if}
				{/foreach}
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE)}
	</form>
	<!-- /tpl-Settings-WidgetsManagement-EditWidget -->
{/strip}
