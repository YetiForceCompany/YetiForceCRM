{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-ApiAddress-ApiConfigModal -->
	<div class="modal-body pb-0">
		<form class="validateForm">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
			<input type="hidden" name="action" value="SaveConfig" />
			<input type="hidden" name="mode" value="provider" />
			<input type="hidden" name="provider" value="{$PROVIDER->getName()}" />
			<div class="row no-gutters">
				<div class="col-sm-18 col-md-12">
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$PROVIDER->getCustomFields($CONFIG)}
						<div class="form-group form-row">
							<label class="col-form-label col-md-3 u-text-small-bold text-left text-md-right">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
								{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
								{if $FIELD_MODEL->get('tooltip')}
									<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</div>
								{/if}
							</label>
							{assign var=LINK value=$FIELD_MODEL->get('link')}
							<div class="col-md-9 fieldValue{if $LINK} input-group{/if}">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=false}
								{if $LINK}
									<div class="input-group-append">
										<a href="{$LINK['url']|escape}" class="btn btn-primary js-popover-tooltip" role="button" rel="noreferrer noopener" target="_blank" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="{App\Language::translate($LINK['title'], $QUALIFIED_MODULE)}" aria-label="{App\Language::translate($LINK['title'], $QUALIFIED_MODULE)}">
											<span class="fas fa-link"></span>
										</a>
									</div>
								{/if}
							</div>

						</div>
					{/foreach}
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-ApiAddress-ApiConfigModal -->
{/strip}
