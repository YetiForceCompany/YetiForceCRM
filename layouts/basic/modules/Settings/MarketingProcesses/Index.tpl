{*/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/*}

<div id="supportProcessesContainer" class=" supportProcessesContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_MARKETING_PROCESSES_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
		<li class="active"><a href="#conversiontoaccount" data-toggle="tab">{vtranslate('LBL_CONVERSION', $QUALIFIED_MODULE)} </a></li>
		<li ><a href="#lead_configuration" data-toggle="tab">{vtranslate('LBL_LEADS', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content layoutContent" style="padding-top: 10px;">
		<div class="tab-pane active" id="conversiontoaccount">
			{assign var=CONVERSION value=$MODULE_MODEL->getConfig('conversion')}
			<div class="well">
				<div class="row">
					<div class="col-xs-3"><label class="">{vtranslate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</label></div>
					<div class="col-xs-1"><input class="configField" type="checkbox" data-type="conversion" name="change_owner" value="1"  {if $CONVERSION['change_owner']=='true'}checked=""{/if} /></div>
					<div class="col-xs-8">
						<span class="alert alert-info pull-right no-margin">
							{vtranslate('LBL_CONVERSION_TO_ACCOUNT_INFO',$QUALIFIED_MODULE)}
						</span>
					</div>
				</div>
			</div>
			<div class="well">
				<div class="row">
					<div class="col-xs-3"><label class="">{vtranslate('LBL_CONVERT_LEAD_MERGE',$QUALIFIED_MODULE)}</label></div>
					<div class="col-xs-1"><input class="configField" type="checkbox" data-type="conversion" name="create_always" value="1"  {if $CONVERSION['create_always']=='true'}checked=""{/if} /></div>
					<div class="col-xs-8">
						<span class="alert alert-info pull-right no-margin">
							{vtranslate('LBL_CONVERT_LEAD_MERGE_ALERT',$QUALIFIED_MODULE)}
						</span>
					</div>
				</div>
				<div class="mappingTable{if $CONVERSION['create_always']!='true'} hide{/if}">
					<br>
					<input class="configField" type="hidden" data-type="conversion" name="mapping" value="">
					<div class="paddingBottom10">
						<button id="addMapping" class="btn btn-success addButton" type="button">
							<span class="glyphicon glyphicon-plus"></span>&nbsp;<strong>{vtranslate('LBL_CONDITION', $QUALIFIED_MODULE)}</strong>
						</button>
						<button id="addMapping" class="pull-right btn btn-success saveMapping" type="button">
							{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
						</button>
					</div>
					<table class="table table-bordered" id="convertLeadMapping">
						<tbody>
							<tr class="blockHeader">
								<th class="blockHeader">{vtranslate('Leads', $QUALIFIED_MODULE)}</th>
								<th class="blockHeader">{vtranslate('Accounts', $QUALIFIED_MODULE)}</th>
							</tr>
							{assign var=MAPPING value=\App\Json::decode($CONVERSION.mapping)}
							{assign var=LEAD_FIELDS value=$LEADS_MODULE_MODEL->getFields()}
							{assign var=ACCOUNT_FIELDS value=$ACCOUNTS_MODULE_MODEL->getFields()}
							{foreach item=MAPPING_ARRAY from=$MAPPING  name="mappingLoop"}
								<tr class="listViewEntries" sequence-number="{$smarty.foreach.mappingLoop.iteration}">
									<td>
										<select class="leadsFields select2 input-sm" name="mapping[{$smarty.foreach.mappingLoop.iteration}][lead]">
											{foreach key=FIELD_NAME item=FIELD_INFO from=$LEAD_FIELDS}
												<option value="{$FIELD_NAME}" {if $FIELD_NAME eq key($MAPPING_ARRAY)} selected {/if}>
													{vtranslate($FIELD_INFO->get('label'), $LEADS_MODULE_MODEL->getName())}
												</option>
											{/foreach}
										</select>
									</td>
									<td>
										<div class="row">
											<div class="col-xs-11">
												<select class="accountsFields select2 input-sm" name="mapping[{$smarty.foreach.mappingLoop.iteration}][account]">
													{foreach key=FIELD_NAME item=FIELD_INFO from=$ACCOUNT_FIELDS}
														<option {if $FIELD_NAME eq current($MAPPING_ARRAY)} selected {/if} value="{$FIELD_NAME}">
															{vtranslate($FIELD_INFO->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
														</option>
													{/foreach}
												</select>
											</div>
											<div class="actionImages">
												<a class='btn'><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle deleteMapping"></span></a>
											</div>
										</div>	
									</td>
								</tr>
							{/foreach}
							<tr class="hide newMapping listViewEntries">
								<td>
									<select class="leadsFields newSelect">
										{foreach key=FIELD_NAME item=FIELD_INFO from=$LEAD_FIELDS}
											<option value="{$FIELD_NAME}">
												{vtranslate($FIELD_INFO->get('label'), $LEADS_MODULE_MODEL->getName())}
											</option>
										{/foreach}
									</select>
								</td>
								<td>
									<div class="row">
										<div class="col-xs-11">
											<select class="accountsFields newSelect">
												{foreach key=FIELD_NAME item=FIELD_INFO from=$ACCOUNT_FIELDS}
													<option value="{$FIELD_NAME}">
														{vtranslate($FIELD_INFO->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
													</option>
												{/foreach}
											</select>
										</div>
										<div class="actionImages">
											<a class='btn'><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle deleteMapping"></span></a>
										</div>
									</div>	
								</td>
							</tr>
						</tbody>
					</table>
				</div>					
			</div>					
		</div>
		<div class='tab-pane' id="lead_configuration">
			{assign var=LEAD value=$MODULE_MODEL->getConfig('lead')}
			<table class="table tableRWD table-bordered table-condensed themeTableColor userTable">
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
						<td class="col-md-6">
							{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance('Leads')->getAccessibleGroups()}
							<select class="chzn-select configField" name="groups" data-type="lead" multiple>
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
						<td class="col-md-6">
							<select class="chzn-select configField" multiple data-type="lead" name="status">
								{foreach  item=ITEM from=App\Fields\Picklist::getPickListValues('leadstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $LEAD['status'])} selected {/if}  >{vtranslate($ITEM,'Leads')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_LEAD_CONVERT_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td class="col-md-6">
							<select class="chzn-select configField" multiple data-type="lead" name="convert_status">
								{foreach  item=ITEM from=App\Fields\Picklist::getPickListValues('leadstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $LEAD['convert_status'])} selected {/if}  >{vtranslate($ITEM,'Leads')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{vtranslate('LBL_CURRENTUSER_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td>
							<input class="configField" type="checkbox" data-type="lead" name="currentuser_status"  {if $LEAD['currentuser_status'] == 'true'}checked{/if} />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
