{*<!--
/*********************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
{if count($DATA) gt 0 }
	<div style="padding:5px;">
		<div class="row">
			<div class="col-md-3"><strong>{vtranslate('Potential Name', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{vtranslate('Sales Stage', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{vtranslate('Related To', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{vtranslate('Assigned To', $RELATED_MODULE)}</strong></div>
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-3"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW.potentialid}">{$ROW.potentialname}</a></div>
				<div class="col-md-3">{vtranslate($ROW.sales_stage, $RELATED_MODULE)}</div>
				<div class="col-md-3">
					{if $ROW.related_to gt 0 }
						<a class="moduleColor_Accounts" href="index.php?module=Accounts&view=Detail&record={$ROW.related_to}" title="{vtranslate('Accounts', 'Accounts')}">{Vtiger_Functions::getCRMRecordLabel($ROW.related_to)}</a>
					{/if}
				</div>
				<div class="col-md-3">{Vtiger_Functions::getOwnerRecordLabel($ROW.smownerid)}</div>
			</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_DATA', $MODULE_NAME)}
	</span>
{/if}
{/strip}
