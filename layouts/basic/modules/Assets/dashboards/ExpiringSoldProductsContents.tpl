{*<!--
/*********************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
{if count($DATA) gt 0 }
	<div style="padding:5px;">
		<div class="row">
			<div class="col-md-4"><strong>{vtranslate('Asset Name', $RELATED_MODULE)}</strong></div>
			<div class="col-md-4"><strong>{vtranslate('Date in Service', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{vtranslate('Parent ID', $RELATED_MODULE)}</strong></div>
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-4"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW.assetsid}">{$ROW.assetname}</a></div>
				<div class="col-md-4">{DateTimeField::convertToUserFormat($ROW.dateinservice)}</div>
				<div class="col-md-3">
					{if $ROW.parent_id gt 0 }
						{assign var="CRMTYPE" value=vtlib\Functions::getCRMRecordType($ROW.parent_id)}
						<a class="moduleColor_{$CRMTYPE}" href="index.php?module={$CRMTYPE}&view=Detail&record={$ROW.parent_id}" title="{vtranslate($CRMTYPE, $CRMTYPE)}">{vtlib\Functions::getCRMRecordLabel($ROW.parent_id)}</a>
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_DATA', $MODULE_NAME)}
	</span>
{/if}
{/strip}
