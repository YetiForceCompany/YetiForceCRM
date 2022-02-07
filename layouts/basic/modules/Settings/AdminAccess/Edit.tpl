{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AdminAccess-Edit -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="validateForm">
			<div class="row">
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURES}
					<div class="form-group col-12">
						<div class="row">
							<label class="col-6 col-form-label fontBold">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
							</label>
							{if !$FIELD_MODEL->isEditableReadOnly() && $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
								<div class="col-6 text-right">
									<button class="btn btn-primary mr-1 btn-sm js-modules-select-all" data-js="click" type="button" data-name="{$FIELD_MODEL->getName()}">
										<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SELECT_ALL', $QUALIFIED_MODULE)}
									</button>
									<button class="btn btn-warning btn-sm js-modules-deselect-all" data-js="click" type="button" data-name="{$FIELD_MODEL->getName()}">
										<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_DESELECT_ALL', $QUALIFIED_MODULE)}
									</button>
								</div>
							{/if}
						</div>
						<div class="row">
							<div class="col-12 controls my-auto">
								<div class="input-group fieldContainer" data-name="{$FIELD_MODEL->getName()}">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) MODULE=$QUALIFIED_MODULE RECORD=null}
								</div>
							</div>
						</div>
					</div>
				{/foreach}
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-AdminAccess-Edit -->
{/strip}
