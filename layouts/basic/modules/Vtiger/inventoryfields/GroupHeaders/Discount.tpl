{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-GroupHeaders-Discount -->
	<div>
		<div class="w-100 custom-control-inline" style="justify-content: right;">
			<div class="">
				<span type="text" class="text-nowrap text-right middle u-font-weight-600 js-inv-container-group-summary" data-sumfield="{lcfirst($FIELD->getType())|escape}"></span>
			</div>
			{assign var=DISCOUNT_MODE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, 'discountmode', Vtiger_Inventory_Model::getDiscountsConfig('default_mode'))}
			<button type="button" class="ml-1 text-nowrap btn btn-light btn-sm border js-change-discount {if $DISCOUNT_MODE !== \Vtiger_Inventory_Model::DISCOUT_MODE_GROUP}d-none{/if}" data-mode="{\Vtiger_Inventory_Model::DISCOUT_MODE_GROUP}">
				<span class="small">
					<span class="fas fa-long-arrow-alt-down"></span>
					<span class="fas fa-percent"></span>
				</span>
			</button>
			{assign var=DISCOUNT_PARAMS value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, 'discountparam')}
			<input type="hidden" value="{if $DISCOUNT_PARAMS}{\App\Purifier::encodeHtml($DISCOUNT_PARAMS)}{/if}" class="discountParam" />
		</div>
	</div>
	<!-- /tpl-Base-inventoryfields-GroupHeaders-Discount -->
{/strip}
