{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}

<div id="supportProcessesContainer" class="container-fluid supportProcessesContainer" style="margin-top:10px;">
 	<h3>{vtranslate('LBL_MARKETING_PROCESSES', $QUALIFIED_MODULE)}</h3>
	{vtranslate('LBL_MARKETING_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}&nbsp;<hr>
	<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
		<li class="active"><a href="#conversiontoaccount" data-toggle="tab">{vtranslate('LBL_CONVERSION', $QUALIFIED_MODULE)} </a></li>
		<li ><a href="#lead_configuration" data-toggle="tab">{vtranslate('LBL_LEADS', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content layoutContent" style="padding-top: 10px;">
		<div class="tab-pane active" id="conversiontoaccount">
			{assign var=CONVERSION value=$MODULE_MODEL->getConfig('conversion')}
			<div class="row-fluid">
				<div class="span3"><label class="span3">{vtranslate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</label></div>
				<div class="span1"><input class="configField" type="checkbox" data-type="conversion" name="change_owner" value="1"  {if $CONVERSION['change_owner']=='true'}checked=""{/if} /></div>
				<div class="span8">
					<span class="alert alert-info pull-right">
						{vtranslate('LBL_CONVERSION_TO_ACCOUNT_INFO',$QUALIFIED_MODULE)}
					</span>
				</div>
			</div>
		</div>
		<div class='tab-pane' id="lead_configuration">
			{assign var=LEAD value=$MODULE_MODEL->getConfig('lead')}
			<table class="table table-bordered table-condensed themeTableColor userTable">
				<thead>
					<tr class="blockHeader" >
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</span>
						</th>
						<th class="mediumWidthType">
							<span>{vtranslate('LBL_VALUES', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><label>{vtranslate('LBL_GROUPS_INFO', $QUALIFIED_MODULE)}</label></td>
						<td>
							{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups('Leads')}
							<select class="chzn-select span8 configField" name="groups" data-type="lead" multiple>
								{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
									<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $LEAD['groups'])}selected{/if} >
									{$OWNER_NAME}
									</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_LEAD_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td>
							<select class="chzn-select span8 configField" multiple data-type="lead" name="status">
								{foreach  item=ITEM from=Vtiger_Util_Helper::getPickListValues('leadstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $LEAD['status'])} selected {/if}  >{vtranslate($ITEM,'Leads')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_CURRENTUSER_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td>
							<input class="span1 configField" type="checkbox" data-type="lead" name="currentuser_status"  {if $LEAD['currentuser_status'] == 'true'}checked{/if} />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
