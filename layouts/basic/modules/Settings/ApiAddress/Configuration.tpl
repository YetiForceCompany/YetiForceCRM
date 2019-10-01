{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div id="tpl-Settings-ApiAddress-Configuration menuEditorContainer">
    <div class="widget_header row mb-2">
        <div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
    </div>
	<div class="main_content">
		<form class="js-validation-form">
			<div class="col-12 form-row m-0">
				<div class="col-12 form-row mb-2">
					<div class="col-sm-6 col-md-4">
						<div >
							{\App\Language::translate('LBL_MIN_LOOKUP_LENGTH', $MODULENAME)}:
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="min_length" type="number" min="0" class="api form-control m-0" value="{$CONFIG['global']['min_length']}"
							data-validation-engine="validate[required,min[0],funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]">
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-12 form-row  mb-2">
					<div class='col-sm-6 col-md-4'>
						<div>
							{\App\Language::translate('LBL_NUMBER_SEARCH_RESULTS', $MODULENAME)}:
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="text-center">
							<input name="result_num" type="number" min="0" class="api form-control m-0" value="{$CONFIG['global']['result_num']}"
							data-validation-engine="validate[required,min[1]funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]">
						</div>
					</div>
				</div>
				<div class="js-config-table table-responsive" data-js="container">
				<hr>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="" scope="col">{\App\Language::translate('LBL_PROVIDER_NAME', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIVE', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_DEFAULT', $MODULENAME)}</th>
								<th class="text-center" scope="col">{\App\Language::translate('LBL_PROVIDER_ACTIONS', $MODULENAME)}</th>
							</tr>
						</thead>
						<tbody>
							{function UNSET_POPOVER}
								class="js-popover-tooltip text-center" data-content="{\App\Language::translate('LBL_PROVIDER_UNSET', $MODULENAME)}" data-placement="top"
							{/function}
							{foreach from=\App\Map\Address::getAllProviders() item=ITEM key=KEY}
								{assign var=CONFIGURED value=$ITEM->isConfigured()}
								<tr>
									<th class="" scope="row">{\App\Language::translate('LBL_PROVIDER_'|cat:$KEY|upper, $MODULENAME)}</th>
									<td {if !$CONFIGURED}{UNSET_POPOVER}{else}class="text-center"{/if}>
										<input name="active" data-type="{$KEY}" type="checkbox"{if !empty($ITEM->config['active'])} checked{/if}{if !$CONFIGURED} disabled{/if}>
									</td>
									<td {if !$CONFIGURED}{UNSET_POPOVER}{else}class="text-center"{/if}>
										<input name="default_provider" value="{$KEY}" type="radio"{if $DEFAULT_PROVIDER eq $KEY && $CONFIGURED} checked{/if}{if !$CONFIGURED} disabled{/if}>
									</td>
									<td class="text-center">
										<button class="btn btn-outline-secondary btn-sm js-show-config-modal js-popover-tooltip mr-1" type="button" data-provider="{$KEY}"
										data-content="{\App\Language::translate('LBL_PROVIDER_CONFIG', $MODULENAME)}">
											<span class="fas fa-cog"></span>
										</button>
										<a href="{$ITEM->getDocUrl()}" class="btn btn-outline-primary btn-sm js-popover-tooltip" role="button" target="_blank"
										data-content="{\App\Language::translate('LBL_PROVIDER_INFO_'|cat:$KEY|upper, $MODULENAME)}">
											<span class="fas fa-link"></span>
										</a>
										{if false}
											<button class="js-validate btn btn-outline-success btn-sm js-popover-tooltip" data-provider="{$KEY}" type="button" data-content="{\App\Language::translate('LBL_VALIDATE', $QUALIFIED_MODULE)}" data-js="click | data | class: fa-spin" >
												<span class="js-validate__icon fas fa-sync fa-xs mr-1"></span>
											</button>
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					<div class="w-100 d-flex justify-content-end mb-2">
						<button type="button" class="btn btn-success saveGlobal"><span class="fa fa-check mr-2"></span>{\App\Language::translate('LBL_SAVE_GLOBAL_SETTINGS', $MODULENAME)}</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
