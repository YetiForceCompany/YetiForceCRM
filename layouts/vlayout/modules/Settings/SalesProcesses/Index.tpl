{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
<div class="" id="salesProcessesContainer" style="margin-top:10px;">
 	<h3>{vtranslate('LBL_SALES_PROCESSES', $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_SALES_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
	&nbsp;<hr>
	<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
		<li class="active"><a href="#popup" data-toggle="tab">{vtranslate('LBL_PRODUCTS_AND_SERVICES_POPUP', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#Calculations" data-toggle="tab">{vtranslate('LBL_CALCULATIONS', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#Potentials" data-toggle="tab">{vtranslate('LBL_POTENTIALS', $QUALIFIED_MODULE)} </a></li>
		<li><a href="#Assets" data-toggle="tab">{vtranslate('LBL_ASSETS', $QUALIFIED_MODULE)} </a></li>
	</ul>
	{assign var=CONFIG value=$MODULE_MODEL->getConfig()}
	<div class="tab-content layoutContent" style="padding-top: 10px;">
		<div class="tab-pane active" id="popup">
			{assign var=POPUP value=$CONFIG['popup']}
			<div class="row">
				<div class="col-md-1 textAlignCenter"><input class="configField" type="checkbox" data-type="popup" name="limit_product_service" id="limit_product_service" value="1"  {if $POPUP['limit_product_service']=='true'}checked=""{/if} /></div>
				<div class="col-md-11"><label for="limit_product_service">{vtranslate('LBL_LIMIT_PRODUCT_AND_SERVICE',$QUALIFIED_MODULE)}</label></div>
			</div>
			<div class="row">
				<div class="col-md-1 textAlignCenter"><input class="configField" type="checkbox" data-type="popup" name="update_shared_permissions" id="update_shared_permissions" value="1"  {if $POPUP['update_shared_permissions']=='true'}checked=""{/if} /></div>
				<div class="col-md-11"><label for="update_shared_permissions">{vtranslate('LBL_UPDATE_SHARED_PERMISSIONS',$QUALIFIED_MODULE)}</label></div>
			</div>
		</div>
		<div class="tab-pane" id="Calculations">
			{assign var=CALCULATION value=$CONFIG['calculation']}
			<table class="table table-bordered table-condensed themeTableColor userTable listViewEntries">
				<tbody>
					<tr>
						<td><label>{vtranslate('LBL_STATUSES_CLOSED_CALCULATION', $QUALIFIED_MODULE)}</label></td>
						<td>
							<select class="chzn-select col-md-8 configField" multiple data-type="calculation" name="calculationsstatus">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('calculationsstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $CALCULATION['calculationsstatus'])} selected {/if}  >{vtranslate($ITEM,'Calculations')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="Potentials">
			{assign var=POTENTIALS value=$CONFIG['potential']}
			<table class="table table-bordered table-condensed themeTableColor userTable listViewEntries">
				<tbody>
					<tr>
						<td><label for="add_potential">{vtranslate('LBL_CREATE_POTENTIALS',$QUALIFIED_MODULE)}</label></td>
						<td><input class="configField" type="checkbox" data-type="potential" name="add_potential" id="add_potential" value="1"  {if $POTENTIALS['add_potential']=='true'}checked=""{/if} /></td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_STATUSES_CLOSED_POTENTIAL', $QUALIFIED_MODULE)}</label></td>
						<td>
							<select class="chzn-select col-md-8 configField" multiple data-type="potential" name="salesstage">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('sales_stage')}
									<option value="{$ITEM}" {if in_array($ITEM, $POTENTIALS['salesstage'])} selected {/if}  >{vtranslate($ITEM,'Potentials')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="Assets">
			{assign var=ASSETS value=$CONFIG['asset']}
			<table class="table table-bordered table-condensed themeTableColor userTable listViewEntries">
				<tbody>
					<tr>
						<td><label>{vtranslate('LBL_STATUSES_CLOSED_ASSETS', $QUALIFIED_MODULE)}</label></td>
						<td>
							<select class="chzn-select col-md-8 configField" multiple data-type="asset" name="assetstatus">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('assetstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $ASSETS['assetstatus'])} selected {/if}>{vtranslate($ITEM,'Assets')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

