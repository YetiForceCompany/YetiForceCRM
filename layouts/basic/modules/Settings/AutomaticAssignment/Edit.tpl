{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AutomaticAssingment-Edit -->
	<div class="verticalScroll ">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="form-horizontal mt-2">
			{assign var=CHECK_ALERT value=\App\YetiForce\Shop::checkAlert('YetiForceAutoAssignment')}
			{if $CHECK_ALERT}
				<div class="alert alert-warning">
					<span class="yfi-premium mr-2 u-fs-2em color-red-600 float-left"></span>
					{\App\Language::translate($CHECK_ALERT, 'Settings::YetiForce')} <a class="btn btn-primary btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceAutoAssignment&mode=showProductModal"><span class="yfi yfi-shop mr-2"></span>{\App\Language::translate('LBL_YETIFORCE_SHOP', $QUALIFIED_MODULE)}</a>
				</div>
			{/if}
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
				<input type="hidden" name="action" value="SaveAjax" />
				{if !empty($RECORD_ID)}
					<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
				{/if}
				{foreach from=$STRUCTURE item=FIELDS key=BLOCK name=structre}
					<div class="js-toggle-panel c-panel" data-js="click">
						<div class="blockHeader c-panel__header py-2">
							<span class="iconToggle fas {if $smarty.foreach.structre.first}fa-chevron-down{else}fa-chevron-right{/if} fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
							<h5>
								<span class="{$RECORD_MODEL->getModule()->getBlockIcon($BLOCK)} mr-2" aria-hidden="true"></span>
								{\App\Language::translate($BLOCK, $QUALIFIED_MODULE)}
							</h5>
						</div>
						<div class="c-panel__body p-2 js-block-content {if !$smarty.foreach.structre.first}d-none{/if}">
							<div class="form-group row mb-0">
								{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME name=field}
									{if $smarty.foreach.structre.first && $smarty.foreach.field.iteration eq 4}
										<div class="w-100 u-fs-10px">&nbsp;</div>
									{/if}
									{if in_array($FIELD_NAME, ['conditions', 'record_limit_conditions'])}
										<div class="col-12 js-field-container">
											<input type="hidden" name="{$FIELD_NAME}" class="js-condition-value" value="[]" />
											<label class="u-text-small-bold mb-0 {if $FIELD_MODEL->get('hideLabel')} d-none{/if}">
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}:
											</label>
											<div class="js-condition-builder-container">
												{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl') MODULE_NAME=$SOURCE_MODULE ADVANCE_CRITERIA=\App\Json::decode($FIELD_MODEL->get('fieldvalue'))}
											</div>
										</div>
									{else}
										<div class="col-12 {if $FIELD_NAME neq 'members'}col-md-4 {/if} mb-2 js-field-container">
											<label class="u-text-small-bold mb-1">
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
												{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
												{if $FIELD_MODEL->get('tooltip')}
													<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
														<span class="fas fa-info-circle"></span>
													</div>
												{/if}:
											</label>
											<div class="fieldValue{if $FIELD_MODEL->getFieldDataType() eq 'boolean'} ml-2 align-top d-inline-block{else} m-auto{/if}">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
											</div>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					</div>
				{/foreach}

				<div class="c-form__action-panel">
					<button class="btn btn-success js-save" type="submit">
						<span class="fas fa-check mr-2"></span>
						{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
					<button class="btn btn-danger ml-2" type="reset" onclick="javascript:window.history.back();">
						<span class="fa fa-times u-mr-5px"></span>
						{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</form>
		</div>
	</div>
	<!-- /tpl-Settings-AutomaticAssingment-Edit -->
{/strip}
