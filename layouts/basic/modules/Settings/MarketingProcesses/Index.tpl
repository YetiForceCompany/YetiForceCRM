{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

<div id="supportProcessesContainer" class=" supportProcessesContainer">
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
		<li class="nav-item"><a class="nav-link active" href="#conversiontoaccount" data-toggle="tab">{\App\Language::translate('LBL_CONVERSION', $QUALIFIED_MODULE)} </a></li>
		<li class="nav-item"><a class="nav-link" href="#lead_configuration" data-toggle="tab">{\App\Language::translate('LBL_LEADS', $QUALIFIED_MODULE)} </a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="conversiontoaccount">
			{assign var=CONVERSION value=$MODULE_MODEL->getConfig('conversion')}
			<div class="well">
				<div class="row">
					<div class="col-3"><label class="">{\App\Language::translate('LBL_CONVERSION_TO_ACCOUNT',$QUALIFIED_MODULE)}</label></div>
					<div class="col-1"><input class="configField" type="checkbox" data-type="conversion" name="change_owner" value="1" {if $CONVERSION['change_owner']=='true'}checked="" {/if} /></div>
					<div class="col-8">
						<span class="alert alert-info float-right no-margin">
							{\App\Language::translate('LBL_CONVERSION_TO_ACCOUNT_INFO',$QUALIFIED_MODULE)}
						</span>
					</div>
				</div>
			</div>
			<div class="well">
				<div class="row">
					<div class="col-3"><label class="">{\App\Language::translate('LBL_CONVERT_LEAD_MERGE',$QUALIFIED_MODULE)}</label></div>
					<div class="col-1"><input class="configField" type="checkbox" data-type="conversion" name="create_always" value="1" {if $CONVERSION['create_always']=='true'}checked="" {/if} /></div>
					<div class="col-8">
						<span class="alert alert-info float-right no-margin">
							{\App\Language::translate('LBL_CONVERT_LEAD_MERGE_ALERT',$QUALIFIED_MODULE)}
						</span>
					</div>
				</div>
				<div class="mappingTable{if $CONVERSION['create_always']!='true'} d-none{/if}">
					<br />
					<input class="configField" type="hidden" data-type="conversion" name="mapping" value="">
					<div class="paddingBottom10">
						<button id="addMapping" class="btn btn-success addButton" type="button">
							<span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('LBL_CONDITION', $QUALIFIED_MODULE)}</strong>
						</button>
						<button id="addMapping" class="float-right btn btn-success saveMapping" type="button">
							{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
						</button>
					</div>
					<table class="table table-bordered" id="convertLeadMapping">
						<tbody>
							<tr class="blockHeader">
								<th class="blockHeader">{\App\Language::translate('Leads', $QUALIFIED_MODULE)}</th>
								<th class="blockHeader">{\App\Language::translate('Accounts', $QUALIFIED_MODULE)}</th>
							</tr>
							{assign var=MAPPING value=\App\Json::decode($CONVERSION.mapping)}
							{assign var=LEAD_FIELDS value=$LEADS_MODULE_MODEL->getFields()}
							{assign var=ACCOUNT_FIELDS value=$ACCOUNTS_MODULE_MODEL->getFields()}
							{foreach item=MAPPING_ARRAY from=$MAPPING  name="mappingLoop"}
								<tr class="listViewEntries" sequence-number="{$smarty.foreach.mappingLoop.iteration}">
									<td>
										<select class="leadsFields select2 form-control-sm" name="mapping[{$smarty.foreach.mappingLoop.iteration}][lead]">
											{foreach key=FIELD_NAME item=FIELD_INFO from=$LEAD_FIELDS}
												<option value="{$FIELD_NAME}" {if $FIELD_NAME eq key($MAPPING_ARRAY)} selected {/if}>
													{\App\Language::translate($FIELD_INFO->get('label'), $LEADS_MODULE_MODEL->getName())}
												</option>
											{/foreach}
										</select>
									</td>
									<td>
										<div class="row">
											<div class="col-11">
												<select class="accountsFields select2 form-control-sm" name="mapping[{$smarty.foreach.mappingLoop.iteration}][account]">
													{foreach key=FIELD_NAME item=FIELD_INFO from=$ACCOUNT_FIELDS}
														<option {if $FIELD_NAME eq current($MAPPING_ARRAY)} selected {/if} value="{$FIELD_NAME}">
															{\App\Language::translate($FIELD_INFO->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
														</option>
													{/foreach}
												</select>
											</div>
											<div class="actionImages">
												<a class='btn'><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt deleteMapping"></span></a>
											</div>
										</div>
									</td>
								</tr>
							{/foreach}
							<tr class="d-none newMapping listViewEntries">
								<td>
									<select class="leadsFields newSelect">
										{foreach key=FIELD_NAME item=FIELD_INFO from=$LEAD_FIELDS}
											<option value="{$FIELD_NAME}">
												{\App\Language::translate($FIELD_INFO->get('label'), $LEADS_MODULE_MODEL->getName())}
											</option>
										{/foreach}
									</select>
								</td>
								<td>
									<div class="row">
										<div class="col-11">
											<select class="accountsFields newSelect">
												{foreach key=FIELD_NAME item=FIELD_INFO from=$ACCOUNT_FIELDS}
													<option value="{$FIELD_NAME}">
														{\App\Language::translate($FIELD_INFO->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
													</option>
												{/foreach}
											</select>
										</div>
										<div class="actionImages">
											<a class='btn'><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt deleteMapping"></span></a>
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
			<table class="table tableRWD table-bordered table-sm themeTableColor userTable">
				<thead>
					<tr class="blockHeader">
						<th class="mediumWidthType">
							<span>{\App\Language::translate('LBL_INFO', $QUALIFIED_MODULE)}</span>
						</th>
						<th class="mediumWidthType">
							<span>{\App\Language::translate('LBL_VALUES', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><label>{\App\Language::translate('LBL_GROUPS_INFO', $QUALIFIED_MODULE)}</label></td>
						<td class="w-50">
							{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance('Leads')->getAccessibleGroups()}
							<select class="select2 configField" name="groups" data-type="lead" multiple>
								{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
									<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $LEAD['groups'])}selected{/if}>
										{$OWNER_NAME}
									</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{\App\Language::translate('LBL_LEAD_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td class="w-50">
							<select class="select2 configField" multiple data-type="lead" name="status">
								{foreach  item=ITEM from=App\Fields\Picklist::getValuesName('leadstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $LEAD['status'])} selected {/if}>{\App\Language::translate($ITEM,'Leads')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td><label>{\App\Language::translate('LBL_LEAD_CONVERT_STATUS', $QUALIFIED_MODULE)}</label></td>
						<td class="w-50">
							<select class="select2 configField" multiple data-type="lead" name="convert_status">
								{foreach  item=ITEM from=App\Fields\Picklist::getValuesName('leadstatus')}
									<option value="{$ITEM}" {if in_array($ITEM, $LEAD['convert_status'])} selected {/if}>{\App\Language::translate($ITEM,'Leads')}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
