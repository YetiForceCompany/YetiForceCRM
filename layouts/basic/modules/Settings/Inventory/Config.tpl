{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form id="configForm">
		<input type="hidden" id="view" value="{$VIEW}" />
		<div class="mb-5">
			<div class="o-breadcrumb widget_header row">
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="contents py-3 form-horizontal">
						<div class="form-group form-row">
							<label class="col-md-3 u-text-small-bold col-form-label text-md-right form-control-plaintext">{\App\Language::translate('LBL_SUMMATION_TYPE', $QUALIFIED_MODULE)}</label>
							<div class="col-md-6">
								<select class="select2 form-control" name="aggregation">
									{foreach  item=LABEL key=KEY from=Settings_Inventory_Module_Model::getPicklistValues('aggregation')}
										<option value="{\App\Purifier::encodeHtml($KEY)}" {if $KEY eq $CONFIG['aggregation']} selected {/if}>{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group form-row">
							{if $VIEW eq 'DiscountConfiguration'}
								{assign var=FIELD value='discounts'}
								{assign var=FIELD_MODEL value=Vtiger_Basic_InventoryField::getInstance('Vtiger', 'DiscountMode')}
							{else}
								{assign var=FIELD value='taxs'}
								{assign var=FIELD_MODEL value=Vtiger_Basic_InventoryField::getInstance('Vtiger', 'TaxMode')}
							{/if}
							{if $CONFIG[$FIELD] neq ''}
								{assign var=FIELD_VALUE value=explode(',', $CONFIG[$FIELD])}
							{else}
								{assign var=FIELD_VALUE value=[]}
							{/if}
							<label class="col-md-3 col-form-label u-text-small-bold text-md-right form-control-plaintext">
								<span class="redColor">*</span>
								{\App\Language::translate('LBL_AVAILABLE_'|cat:strtoupper($FIELD), $QUALIFIED_MODULE)}
							</label>
							<div class="col-md-9">
								<select class="select2 form-control" multiple name="{$FIELD}" data-prevvalue='{implode(',', $FIELD_VALUE)}' data-validation-engine="validate[required]">
									{foreach  item=LABEL key=KEY from=Settings_Inventory_Module_Model::getPicklistValues($FIELD)}
										<option value="{\App\Purifier::encodeHtml($KEY)}" {if in_array($KEY, $FIELD_VALUE)} selected {/if}>{\App\Language::translate("{$LABEL}_"|cat:strtoupper($FIELD), $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group form-row">
							<label class="col-md-3 u-text-small-bold col-form-label text-md-right form-control-plaintext">{\App\Language::translate('LBL_DEFAULT_MODE', $QUALIFIED_MODULE)}</label>
							<div class="col-md-9">
								<select class="select2 form-control" name="default_mode">
									{foreach from=$FIELD_MODEL->getModes() item=LABEL key=KEY}
										<option value="{$KEY}" {if $CONFIG['default_mode'] == $KEY}selected{/if}>{\App\Language::translate($LABEL)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					{if $VIEW eq 'DiscountConfiguration'}
						<div class="alert alert-info">
							<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_DISCOUNT_CONFIG_TITLE', $QUALIFIED_MODULE)}</h5>
							<p>{\App\Language::translate('LBL_ALERT_DISCOUNT_CONFIG_DESC', $QUALIFIED_MODULE)}</p>
						</div>
					{elseif $VIEW eq 'TaxConfiguration'}
						<div class="alert alert-info">
							<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_TAX_CONFIG_TITLE', $QUALIFIED_MODULE)}</h5>
							<p>{\App\Language::translate('LBL_ALERT_TAX_CONFIG_DESC', $QUALIFIED_MODULE)}</p>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</form>
{/strip}
