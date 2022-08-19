{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-Edit -->
	<div class="editViewContainer">
		<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
			<input type="hidden" name="module" value="Groups">
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
			<div class="o-breadcrumb widget_header row mb-3">
				<div class="col-12 d-flex">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			{foreach from=$STRUCTURE item=FIELD_MODEL key=FIELD_NAME name=structre}
				{if $FIELD_NAME === 'modules'}
					<div class="col-md-8 text-right mb-2">
						<button class="btn btn-success mr-1 btn-sm js-modules-select-all" data-js="click" type="button">
							<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SELECT_ALL', $QUALIFIED_MODULE)}
						</button>
						<button class="btn btn-danger btn-sm js-modules-deselect-all" data-js="click" type="button">
							<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_DESELECT_ALL', $QUALIFIED_MODULE)}
						</button>
					</div>
				{/if}
				<div class="form-group row">
					<label class="u-font-weight-600 col-lg-2 textAlignRight align-self-center">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
						{if $FIELD_MODEL->get('tooltip')}
							<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
								<span class="fas fa-info-circle"></span>
							</div>
						{/if}:
					</label>
					<div class="fieldValue col-lg-6">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=$RECORD_MODEL}
					</div>
				</div>
			{/foreach}
			<div class="form-group row">
				<div class="text-right col-lg-8">
					<button class="btn btn-success mr-1 c-btn-block-sm-down mb-1 mb-md-0" type="submit"><span
							class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
					<button class="btn btn-danger c-btn-block-sm-down" type="reset"
						onclick="javascript:window.history.back();"><span
							class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Groups-Edit -->
{/strip}
