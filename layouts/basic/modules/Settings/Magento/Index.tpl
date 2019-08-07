{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Magento-Index">
		<div class="o-breadcrumb widget_header mb-2 d-flex flex-nowrap flex-md-wrap justify-content-between px-2 row">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class='editViewContainer'>
			<form class="js-edit-form form-horizontal recordEditView" method="post" action="index.php"
				  data-js="submit|validationEngine">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE_NAME}">
				<input type="hidden" name="action" value="Save">
				<div class="blockHeader">
					<h5>{\App\Language::translate('LBL_MAGENTO_SETTINGS', $QUALIFIED_MODULE)}</h5>
				</div>
				{foreach from=$RECORD_MODEL->getModule()->getFormFields() item=FIELD_DATA key=FIELD_NAME name=fields}
					{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
					<div class="form-group row">
						<label class="col-form-label col-lg-4 u-text-small-bold text-right">
							{\App\Language::translate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
							{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
							{if !empty($FIELD_DATA['tooltip'])}
								&nbsp;
								<span class="js-popover-tooltip" data-js="popover" data-placement="top"
									  data-content="{\App\Purifier::encodeHtml(\App\Language::translate($FIELD_MODEL->get('label')|cat:"_TOOLTIP", $QUALIFIED_MODULE))}">
									<span class="fas fa-info-circle"></span>
								</span>
							{/if}
							:
						</label>
						<div class="col-lg-6 fieldValue">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE}
						</div>
					</div>
				{/foreach}
				<div class="tpl-EditViewActions c-form__action-panel">
					{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
					<button class="btn btn-success" type="submit">
						<span class="fas fa-check u-mr-5px"></span>
						<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
					</button>
					{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='editViewHeader'}
					{/foreach}
				</div>
			</form>
		</div>
	</div>
{/strip}
