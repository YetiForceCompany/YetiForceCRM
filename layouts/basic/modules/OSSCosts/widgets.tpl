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
<div>
	<div class="row">
		<div class="col-md-5">{vtranslate('summary_count', $MODULENAME)}: {$RECOLDLIST['summary'][0]['count']}</div>
		<div class="col-md-7">{vtranslate('summary_sum', $MODULENAME)}: {CurrencyField::convertToUserFormat( $RECOLDLIST['summary'][0]['sum'] )}</div>
	</div>
	<hr/>
	<div class="row">
		<div class="col-md-6">{vtranslate('Costs_no', $MODULENAME)}</div>
		<div class="col-md-6">{vtranslate('Total', $MODULENAME)}</div>
	</div>
	{foreach from=$RECOLDLIST['rows'] key=key item=item}
		<div class="row">
			<div class="col-md-6">{$item['osscosts_no']}</div>
			<div class="col-md-6">{CurrencyField::convertToUserFormat( $item['total'] )}</div>
		</div>
	{/foreach}
</div>