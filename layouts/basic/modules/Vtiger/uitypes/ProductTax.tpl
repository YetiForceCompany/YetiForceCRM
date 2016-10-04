{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var="tax_count" value=1}
{foreach item=tax key=count from=$TAXCLASS_DETAILS}
	{if $tax.check_value eq 1}
		{assign var=check_value value="checked"}
		{assign var=show_value value="visible"}
	{else}
		{assign var=check_value value=""}
		{assign var=show_value value="hidden"}
	{/if}
	{if $tax_count gt 1}
	</div><div class="col-md-6 fieldRow">
	<div class="fieldLabel {$WIDTHTYPE} col-md-3">
		<label class="muted pull-right marginRight10px">
	{/if}
			<span class="taxLabel alignBottom">{vtranslate($tax.taxlabel, $MODULE)}<span class="paddingLeft10px"> (%)</span></span>
		</label>
	</div>
	<div class="fieldValue {$WIDTHTYPE} col-md-9">
		<div class="row">
			<div class="input-group">
				<span class="input-group-addon">
						<input type="checkbox" name="{$tax.check_name}" id="{$tax.check_name}" title="{vtranslate($tax.taxlabel, $MODULE)}" class="taxes" data-tax-name={$tax.taxname} {$check_value}>
				</span>
				<input type="text" {if $show_value eq 'hidden'} readonly="readonly"{/if} class="detailedViewTextBox form-control muted" name="{$tax.taxname}" value="{$tax.percentage}" title="{$tax.percentage}" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" />
			</div>
		</div>
	</div>
	{assign var="tax_count" value=$tax_count+1}
	{if $COUNTER eq 2}
	</div><div class="col-md-12>"
		{assign var="COUNTER" value=1}
	{else}
		{assign var="COUNTER" value=$COUNTER+1}
	{/if}
{/foreach}
{/strip}
