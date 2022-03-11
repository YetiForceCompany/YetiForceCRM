{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ServiceContracts-SlaPolicy -->
	<div class="js-sla-policy relatedContainer" data-js="container">
		<input type="hidden" name="target" value="{$TARGET_MODULE}">
		<div class="form-group row text-center">
			<div class="col-12 flex">
				<label class="d-inline-block mr-2">
					{\App\Language::translate('LBL_POLICY_TYPE', $MODULE_NAME)}:
				</label>
				<label class="d-inline-block mr-2">
					<input type="radio" name="policy_type" class="form-control d-inline-block mr-1 js-sla-policy-type-radio" value="0" {if $POLICY_TYPE===0} checked="checked" {/if} />{\App\Language::translate('LBL_POLICY_TYPE_GLOBAL', $MODULE_NAME)}
				</label>
				<label class="d-inline-block mr-2">
					<input type="radio" name="policy_type" class="form-control d-inline-block mr-1 js-sla-policy-type-radio" value="1" {if $POLICY_TYPE===1} checked="checked" {/if} />{\App\Language::translate('LBL_POLICY_TYPE_TEMPLATE', $MODULE_NAME)}
				</label>
				<label class="d-inline-block mr-2">
					<input type="radio" name="policy_type" class="form-control d-inline-block mr-1 js-sla-policy-type-radio" value="2" {if $POLICY_TYPE===2} checked="checked" {/if} /> {\App\Language::translate('LBL_POLICY_TYPE_CUSTOM', $MODULE_NAME)}
				</label>
				<button class="js-sla-policy-custom js-sla-policy-add-record-btn btn btn-success float-right d-none" data-record-id="{$RECORD->getId()}">
					<span class="fas fa-plus"></span> {\App\Language::translate('LBL_ADD_ENTRY', $MODULE_NAME)}
				</button>
			</div>
		</div>
		<div class="js-sla-policy-template js-sla-policy-template--container form-group row d-none" data-js="container"></div>
		<div class="js-sla-policy-custom form-group row d-none" data-js="container">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('CustomConditions.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="row">
			<div class="col text-center">
				<button class="btn btn-success js-sla-policy-save-btn">
					<span class="fas fa-check mr-2"></span>
					{\App\Language::translate('LBL_SAVE')}
				</button>
			</div>
		</div>
	</div>
	<!-- /tpl-ServiceContracts-SlaPolicy -->
{/strip}
