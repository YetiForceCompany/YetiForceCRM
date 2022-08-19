{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-TreesManager-EditView -->
	<div class="editViewContainer">
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
			<input type="hidden" name="module" value="TreesManager" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" id="treeLastID" value="{$LAST_ID}" />
			<input type="hidden" id="access" value="{$ACCESS}" />
			<input type="hidden" name="tree" id="treeValues" value='{\App\Purifier::encodeHtml($TREE)}' />
			<input type="hidden" name="replace" id="replaceIds" value="" />
			<div class='widget_header row '>
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="form-group row">
				{foreach from=$RECORD_MODEL->getEditViewStructure() item=FIELD_MODEL key=FIELD_NAME name=field}
					<div class="col-12 col-md-6 mb-2 js-field-container">
						<label class="u-text-small-bold  mb-1">
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
							{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
							{if $FIELD_MODEL->get('tooltip')}
								<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
									<span class="fas fa-info-circle"></span>
								</div>
							{/if}:
						</label>
						<div class="fieldValue m-auto">
							{if $FIELD_MODEL->isEditableReadOnly()}
								<input type="text" disabled="disabled" class="form-control" value="{\App\Purifier::encodeHtml($RECORD_MODEL->getDisplayValue($FIELD_MODEL->getName()))}" />
							{else}
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
							{/if}
						</div>
					</div>
				{/foreach}
			</div>
			<hr>
			<div class="row align-items-center">
				<div class="col-md-3">
					<label class=""><strong>{\App\Language::translate('LBL_ADD_ITEM_TREE', $QUALIFIED_MODULE)}</strong></label>
				</div>
				<div class="col-md-8 d-flex">
					<input type="text" class="fieldValue col-md-4 addNewElement form-control">
					<button class="btn btn-primary addNewElementBtn ml-1 noWrap" type="button">
						<span class="fas fa-plus u-mr-5px"></span><strong>{\App\Language::translate('LBL_ADD_TO_TREES', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			</div>
			<hr class="mt-1">
			<div class="modal-header" tabindex="-1">
				<div id="treeContents"></div>
			</div>
			<br />
			<div class="float-right">
				<button class="btn btn-success saveTree mr-1">
					<span class="fas fa-check mr-1"></span>
					<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
				</button>
				<button class="cancelLink btn btn-danger" type="reset" onclick="javascript:window.history.back();">
					<span class="fas fa-times"></span>&nbsp;&nbsp;
					<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
				</button>
			</div>
			<div class="clearfix"></div>
	</div>
	<!-- /tpl-Settings-TreesManager-EditView -->
{/strip}
