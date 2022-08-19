{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-DependentFields -->
	<div class="form-group row mb-0">
		{foreach from=$STRUCTURE item=FIELD_MODEL name=field}
			<div class="col-12 col-md-4 mb-2 js-field-container">
				<label class="u-text-small-bold mb-1">
					{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
					{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
					{if $FIELD_MODEL->get('tooltip')}
						<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</div>
					{/if}:
				</label>
				<div class="fieldValue m-auto">
					{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
				</div>
			</div>
		{/foreach}
	</div>
	<!-- /tpl-Settings-PickListDependency-DependentFields -->
{/strip}
