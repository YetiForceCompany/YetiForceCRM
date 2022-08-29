{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryAddItem -->
	<div class="mt-n1">
		{foreach item=MAIN_MODULE from=$BASIC_FIELD->getModules() name=moduleList}
			{if \App\Module::isModuleActive($MAIN_MODULE)}
				<div class="btn-group btn-group-sm align-items-center justify-content-center {if !$smarty.foreach.moduleList.first}ml-lg-1{/if}" role="group">
					<button type="button" data-module="{$MAIN_MODULE}"
						title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)} {\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}"
						class="btn btn-light js-inv-add-item border mt-1 text-nowrap"
						data-js="click">
						<span class="moduleIcon yfm-{$MAIN_MODULE} mr-1"></span><strong>{\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
					</button>
					{assign var=MASS_ADD_URL value=$BASIC_FIELD->getUrlForMassSelection($MAIN_MODULE)}
					{if $MASS_ADD_URL}
						<button type="button" data-module="{$MAIN_MODULE}" data-url="{$MASS_ADD_URL}"
							title="{\App\Language::translate($MAIN_MODULE, $MAIN_MODULE)}"
							data-content="{\App\Language::translate('LBL_MASS_ADD_ENTIRIES', $MODULE_NAME)}"
							class="btn btn-light js-mass-add border mt-1 mr-2 u-cursor-pointer js-popover-tooltip" data-js="popover"
							data-js="click">
							<span class="fas fa-search-plus"></span>
						</button>
					{/if}
				</div>
			{/if}
		{/foreach}
	</div>
	<!-- /tpl-Base-Edit-InventoryAddItem -->
{/strip}
