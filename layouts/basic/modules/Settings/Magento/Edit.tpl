{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Magento-Edit -->
	<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
		<div class="o-breadcrumb__container">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer">
		<div class="alert alert-warning">
			<span class="mdi mdi-information-outline mr-2 u-fs-3x float-left"></span>
			{\App\Language::translate('LBL_MAGENTO_ALERT', $QUALIFIED_MODULE)}<br>
		</div>
		<form class="js-edit-form form-horizontal recordEditView" method="post" id="EditView" action="index.php" data-js="submit|validationEngine">
			<input type="hidden" name="parent" value="Settings">
			<input type="hidden" name="module" value="{$MODULE_NAME}">
			<input type="hidden" name="action" value="SaveAjax">
			<input type="hidden" name="mode" value="save">
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<table class="table table-bordered table-sm mb-5">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="mediumWidthType">
							<span class="fab fa-magento mr-2"></span>
							{\App\Language::translate('LBL_MAGENTO_SETTINGS', $QUALIFIED_MODULE)}
						</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$RECORD_MODEL->getModule()->getFormFields() item=FIELD_DATA key=FIELD_NAME name=fields}
						{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
						<tr>
							<td class="u-w-37per text-right">
								<label class="u-text-small-bold col-form-label">
									{\App\Language::translate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
									{if $FIELD_MODEL->isMandatory()}<span class="redColor ml-1">*</span>{/if}
									{if !empty($FIELD_DATA['tooltip'])}
										<span class="fas fa-info-circle u-cursor-pointer text-primary ml-1 js-popover-tooltip" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate($FIELD_MODEL->get('label')|cat:"_TOOLTIP", $QUALIFIED_MODULE))}" data-placement="top"></span>
									{/if}
								</label>
							</td>
							<td>
								<div class="fieldValue custom-control p-0">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=false}
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="tpl-EditViewActions c-form__action-panel ml-0">
				<button class="btn btn-success" type="submit">
					<span class="fas fa-check u-mr-5px"></span>
					<strong>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}</strong>
				</button>
				<button class="btn btn-danger ml-2" type="reset" onclick="javascript:window.history.back();">
					<span class="fa fa-times u-mr-5px"></span>
					{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Magento-Index -->
{/strip}
