{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="view" value="{$VIEW}" />
	<div class="" id="inventoryConfig">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		{if $VIEW eq 'DiscountConfiguration'}
			<div class="alert alert-info">
				<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_DISCOUNT_CONFIG_TITLE', $QUALIFIED_MODULE)}</h5>
				<p>{\App\Language::translateArgs('LBL_ALERT_DISCOUNT_CONFIG_DESC', $QUALIFIED_MODULE,App\Config::main('site_URL'))}</p>
			</div>
		{elseif $VIEW eq 'TaxConfiguration'}
			<div class="alert alert-info">
				<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_TAX_CONFIG_TITLE', $QUALIFIED_MODULE)}</h5>
				<p>{\App\Language::translateArgs('LBL_ALERT_TAX_CONFIG_DESC', $QUALIFIED_MODULE,App\Config::main('site_URL'))}</p>
			</div>
		{/if}
		<div class="contents mt-3 form-horizontal">
			<div class="form-group form-row">
				<label class="col-md-3 u-text-small-bold col-form-label text-md-right form-control-plaintext">{\App\Language::translate('LBL_SUMMATION_TYPE', $QUALIFIED_MODULE)}</label>
				<div class="col-md-6">
					<select class="select2" name="aggregation">
						{foreach  item=LABEL key=KEY from=Settings_Inventory_Module_Model::getPicklistValues('aggregation')}
							<option value="{$KEY}" {if $KEY eq $CONFIG.aggregation} selected {/if}>{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group form-row">
				{if $VIEW eq 'DiscountConfiguration'}
					{assign var=FIELD value='discounts'}
				{else}
					{assign var=FIELD value='taxs'}
				{/if}
				{assign var=FIELD_VALUE value=explode(',',$CONFIG[$FIELD])}
				<label class="col-md-3 col-form-label u-text-small-bold text-md-right form-control-plaintext">{\App\Language::translate('LBL_AVAILABLE_'|cat:strtoupper($FIELD), $QUALIFIED_MODULE)}</label>
				<div class="col-md-6">
					<select class="select2" multiple name="{$FIELD}">
						{foreach  item=LABEL key=KEY from=Settings_Inventory_Module_Model::getPicklistValues($FIELD)}
							<option value="{$KEY}" {if in_array($KEY, $FIELD_VALUE)} selected {/if}>{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
{/strip}
