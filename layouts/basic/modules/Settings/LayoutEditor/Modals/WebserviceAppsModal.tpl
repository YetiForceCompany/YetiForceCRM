{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Modals-WebserviceAppsModal -->
	<form name="importList" class="js-validate-form js-form-ajax-submit js-modal-form form-horizontal validateForm" action="index.php" method="post" class="form-horizontal" enctype="multipart/form-data" data-js="container">
		<div class="modal-body">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="module" value="LayoutEditor" />
			<input type="hidden" name="action" value="WebserviceApps" />
			<input type="hidden" name="mode" value="update" />
			<input type="hidden" name="fieldId" value="{$FIELD_ID}" />
			<input type="hidden" name="wa" value="{$WEBSERVICE_APP}" />
			<div class="form-group row">
				<label class="col-form-label col-md-3 u-text-small-bold text-left text-lg-right text-md-right">
					{\App\Language::translate('LBL_NAME_FIELD', $QUALIFIED_MODULE)}:
				</label>
				<div class="col-sm-9">
					<input type="text" readonly class="form-control-plaintext" value="{$FIELD_MODEL->getFullLabelTranslation()}" />
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 u-text-small-bold text-left text-lg-right text-md-right">
					{\App\Language::translate('LBL_WSA_VISIBILITY', $QUALIFIED_MODULE)}:
				</label>
				<div class="col-sm-9">
					<select class="select2 form-control" name="visibility" data-validation-engine="validate[required]">
						{foreach key=KEY item=VALUE from=Settings_LayoutEditor_Field_Model::WEBSERVICE_APPS_VISIBILITY}
							<option value="{$KEY}" {if isset($DATA['visibility']) && $KEY == $DATA['visibility']}selected{/if}>{\App\Language::translate($VALUE, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 u-text-small-bold text-left text-lg-right text-md-right">
					{\App\Language::translate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}:
				</label>
				<div class="col-sm-9">
					<input type="checkbox" name="is_default" class="form-control js-default-value" {if !empty($DATA['is_default'])}checked{/if} {if !$FIELD_MODEL->isDefaultValueForWebservice()}readonly="readonly" {/if} value="1" data-js="container" />
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 u-text-small-bold text-left text-lg-right text-md-right"></label>
				<div class="col-md-9">
					<div class="js-default-value-container {if empty($DATA['is_default'])}d-none{/if}" data-js="container">
						{if $FIELD_MODEL->isDefaultValueForWebservice()}
							{assign var=DEFAULT_VALUE_LIST value=\App\Field::getCustomListForDefaultValue($FIELD_MODEL)}
							{if $DEFAULT_VALUE_LIST}
								<div class="mb-3">
									<select class="select2 form-control" name="customDefaultValue" data-validation-engine="validate[required]">
										{foreach key=KEY item=VALUE from=$DEFAULT_VALUE_LIST}
											<option value="{$KEY}" {if isset($DATA['default_value']) && $KEY == $DATA['default_value']}selected{/if}>{$VALUE}</option>
										{/foreach}
									</select>
								</div>
							{else}
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDefaultEditTemplateName(), $FIELD_MODEL->getModuleName())}
							{/if}
						{/if}
					</div>
				</div>
			</div>
		</div>
	</form>
	<!-- /tpl-Settings-LayoutEditor-Modals-WebserviceAppsModal -->
{/strip}
