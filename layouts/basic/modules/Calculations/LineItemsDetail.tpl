{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{assign var=FINAL_DETAILS value=$RELATED_PRODUCTS.1.final_details}
<table class="table table-bordered mergeTables">
    <thead>
    <th colspan="3" class="detailViewBlockHeader">
	{vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}
    </th>
    <th colspan="2" class="detailViewBlockHeader">
	{assign var=CURRENCY_INFO value=$RECORD->getCurrencyInfo()}
	{vtranslate('LBL_CURRENCY', $MODULE_NAME)} : {vtranslate($CURRENCY_INFO['currency_name'],$MODULE_NAME)}({$CURRENCY_INFO['currency_symbol']})
    </th>
    <th colspan="2" class="detailViewBlockHeader">

    </th>
	</thead>
	<tbody>
    <tr>
		<td>
			<span class="redColor">*</span><strong>{vtranslate('LBL_ITEM_NAME',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('LBL_QTY',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('LBL_UNIT',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('LBL_LIST_PRICE',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('LBL_RBH',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('LBL_TOTAL',$MODULE_NAME)}</strong>
		</td>
		<td>
			<strong>{vtranslate('ProductComments',$MODULE_NAME)}</strong>
		</td>
    </tr>
    {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
	<tr>
	    <td>
		<div>
		    {$LINE_ITEM_DETAIL["productName$INDEX"]}
		</div>
		{if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
			<div class="row redColor deletedItem">
				{if empty($LINE_ITEM_DETAIL["productName$INDEX"])}
					{vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE_NAME)}
				{else}
					{vtranslate('LBL_THIS',$MODULE_NAME)} {vtranslate($LINE_ITEM_DETAIL["entityType$INDEX"])} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE_NAME)}
				{/if}
			</div>
		{/if}
		{if !empty($LINE_ITEM_DETAIL["subProductArray$INDEX"])}
		    <div>
			{foreach item=SUB_PRODUCT_NAME from=$LINE_ITEM_DETAIL["subProductArray$INDEX"]}
			    <div>
				{if !empty($SUB_PRODUCT_NAME)}
					- &nbsp; <em>{$SUB_PRODUCT_NAME}</em>
				{/if}
			    </div>
			{/foreach}
		    </div>
		{/if}
	    </td>
	    <td>
			{$LINE_ITEM_DETAIL["qty$INDEX"]}
	    </td>
		<td>
			{$LINE_ITEM_DETAIL["usageUnit$INDEX"]}
		</td>
	    <td>
			<div>
				{$LINE_ITEM_DETAIL["listPrice$INDEX"]}
				{if $MODULE == 'Quotes' || $MODULE == 'SalesOrder'}
					<br />{vtranslate('LBL_PURCHASE',$MODULE)}:<br />
					{$LINE_ITEM_DETAIL["purchase$INDEX"]}
					<br />{vtranslate('LBL_MARGIN',$MODULE)}:<br />
					{$LINE_ITEM_DETAIL["margin$INDEX"]}
					<br />{vtranslate('LBL_MARGINP',$MODULE)}:<br />
					{$LINE_ITEM_DETAIL["marginp$INDEX"]}
				{/if}
			</div>
		</td>
		<td>
		    <div>
			{$LINE_ITEM_DETAIL["rbh$INDEX"]}
		    </div>
		</td>
		<td>
		    <div>
			{$LINE_ITEM_DETAIL["productTotal$INDEX"]}
		    </div>
		</td>
		<td>
			{if !empty($LINE_ITEM_DETAIL["comment$INDEX"]) && $LINE_ITEM_DETAIL["comment$INDEX"] != ' '}
				<div>
					{$LINE_ITEM_DETAIL["comment$INDEX"]}
				</div>
			{/if}
		</td>
	</tr>
	{/foreach}
	</tbody>
	</table>
	<table class="table table-bordered">
	    <tr>
			<td width="83%">
				<span class="pull-right">
				<strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE_NAME)}</strong>
				</span>
			</td>
			<td>
				<span class="pull-right">
				{$FINAL_DETAILS["grandTotal"]}
				</span>
			</td>
	    </tr>
	    <tr>
			<td width="83%">
				<span class="pull-right">
				<strong>{vtranslate('Total Purchase',$MODULE_NAME)}</strong>
				</span>
			</td>
			<td>
				<span class="pull-right">
				{$FINAL_DETAILS["total_purchase"]}
				</span>
			</td>
	    </tr>
	    <tr>
			<td width="83%">
				<span class="pull-right">
				<strong>{vtranslate('Total margin',$MODULE_NAME)}</strong>
				</span>
			</td>
			<td>
				<span class="pull-right">
				{$FINAL_DETAILS["total_margin"]}
				</span>
			</td>
	    </tr>
	    <tr>
			<td width="83%">
				<span class="pull-right">
				<strong>{vtranslate('Total margin Percentage',$MODULE_NAME)}</strong>
				</span>
			</td>
			<td>
				<span class="pull-right">
				{$FINAL_DETAILS["total_marginp"]}
				</span>
			</td>
	    </tr>
	</table>
	
