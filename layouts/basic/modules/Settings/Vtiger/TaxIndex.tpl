{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="" id="TaxCalculationsContainer">
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="contents row">
		<div class="col-md-12">
			{assign var=CREATE_TAX_URL value=$TAX_RECORD_MODEL->getCreateTaxUrl()}
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<div class="marginBottom10px">
				<button type="button" class="btn btn-default addTax addButton" data-url="{$CREATE_TAX_URL}" data-type="0"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}</strong></button>
			</div>
			<table class="table tableRWD table-bordered inventoryTaxTable themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th colspan="3" class="{$WIDTHTYPE}">
							{vtranslate('LBL_PRODUCT_SERVICE_TAXES', $QUALIFIED_MODULE)}
						</th>
					</tr>
				</thead>
			</table>
			<table data-tablesaw-mode="stack" class="table table-bordered inventoryTaxTable themeTableColor">
				<thead>
					<tr>
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><strong>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</strong></th>
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</strong></th>
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach item=PRODUCT_SERVICE_TAX_MODEL from=$PRODUCT_AND_SERVICES_TAXES}
						<tr class="opacity" data-taxid="{$PRODUCT_SERVICE_TAX_MODEL->get('taxid')}" data-taxtype="{$PRODUCT_SERVICE_TAX_MODEL->getType()}">
							<td class="textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><label class="taxLabel">{$PRODUCT_SERVICE_TAX_MODEL->getName()}</label></td>
							<td class="textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><span class="taxPercentage">{$PRODUCT_SERVICE_TAX_MODEL->getTax()}%</span></td>
							<td class="textAlignCenter {$WIDTHTYPE}" style="border-left: none;"><input type="checkbox" class="editTaxStatus" {if !$PRODUCT_SERVICE_TAX_MODEL->isDeleted()}checked{/if} />
								<div class="pull-right actions">
									<a class="editTax cursorPointer" data-url="{$PRODUCT_SERVICE_TAX_MODEL->getEditTaxUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignBottom"></i></a>&nbsp;
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
{/strip}
