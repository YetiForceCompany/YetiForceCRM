{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Magento-Index -->
<div >
	<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
		<div class="o-breadcrumb__container">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class='editViewContainer'>
		<form class="js-edit-form form-horizontal recordEditView" method="post" action="index.php" data-js="submit|validationEngine">
			<input type="hidden" name="parent" value="Settings">
			<input type="hidden" name="module" value="{$MODULE_NAME}">
			<input type="hidden" name="action" value="Save">
			<table class="table table-bordered table-sm mb-5">
				<thead>
					<tr class="blockHeader"><th colspan="2" class="mediumWidthType">
						<span class="fab fa-magento mr-2"></span>
						{\App\Language::translate('LBL_MAGENTO_SETTINGS', $QUALIFIED_MODULE)}
					</th></tr>
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
							<div class="fieldValue">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=false}
							</div>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			<div class="tpl-EditViewActions c-form__action-panel">
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
				<button class="btn btn-success" type="submit">
					<span class="fas fa-check u-mr-5px"></span>
					<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
				</button>
			</div>
		</form>
	</div>
</div>
<!-- /tpl-Settings-Magento-Index -->
{/strip}
