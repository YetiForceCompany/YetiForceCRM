{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="">
		<form id="leadsMapping" method="POST">
			<div class="row widget_header settingsHeader mb-1 align-items-center">
				<span class="col-sm-12 col-12 col-md-8">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
				</span>
				<span class="col-12 col-sm-12 col-md-4 ">
					<span class="float-right">
						<button type="submit" class="btn btn-success">
							<strong>
								<span class="fas fa-check mr-1"></span>
								{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
							</strong>
						</button>
						<button class="cancelLink btn btn-danger" type="reset" onclick="javascript:window.history.back();">
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</button>
					</span>
				</span>
			</div>
			<div class="contents table-responsive" id="detailView">
				<table class="table customTableRWD table-bordered" id="convertLeadMapping">
					<thead>
						<tr class="blockHeader">
							<th data-hide='phone,' class="blockHeader">{\App\Language::translate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
							<th data-hide='phone' class="blockHeader">{\App\Language::translate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
							<th data-hide='phone' class="blockHeader">{\App\Language::translate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
						</tr>
						<tr>
							{foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders() name=header}
								<th {if $smarty.foreach.header.iteration > 2}data-hide='phone' {/if}><b>{\App\Language::translate($LABEL, $LABEL)}</b></th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach key=MAPPING_ID item=MAPPING_ARRAY from=$MODULE_MODEL->getMapping()  name="mappingLoop"}
							<tr class="listViewEntries" sequence-number="{$smarty.foreach.mappingLoop.iteration}">
								<td>
									<input type="hidden" name="mapping[{$smarty.foreach.mappingLoop.iteration}][mappingId]" value="{$MAPPING_ID}" />
									<select class="leadsFields select2" name="mapping[{$smarty.foreach.mappingLoop.iteration}][lead]">
										<option data-type="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
										{foreach key=FIELD_TYPE item=FIELDS_INFO from=$LEADS_MODULE_MODEL->getFields()}
											{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
												<option data-type="{$FIELD_TYPE}" {if $FIELD_ID eq $MAPPING_ARRAY['Leads']['id']} selected {/if} label="{\App\Language::translate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
													{\App\Language::translate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}
												</option>
											{/foreach}
										{/foreach}
									</select>
								</td>
								<td class="selectedFieldDataType textAlignCenter">{\App\Language::translate($MAPPING_ARRAY['Leads']['fieldDataType'], $QUALIFIED_MODULE)}</td>
								<td>
									<select class="accountsFields select2" name="mapping[{$smarty.foreach.mappingLoop.iteration}][account]">
										<option data-type="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
										{foreach key=FIELD_TYPE item=FIELDS_INFO from=$ACCOUNTS_MODULE_MODEL->getFields()}
											{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
												{if $MAPPING_ARRAY['Leads']['fieldDataType'] eq $FIELD_TYPE}
													<option data-type="{$FIELD_TYPE}" {if isset($MAPPING_ARRAY['Accounts']['id']) && $FIELD_ID eq $MAPPING_ARRAY['Accounts']['id']} selected {/if} label="{\App\Language::translate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
														{\App\Language::translate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
													</option>
												{/if}
											{/foreach}
										{/foreach}
									</select>
								</td>
							</tr>
						{/foreach}
						<tr class="d-none newMapping listViewEntries bg-warning">
							<td>
								<select class="leadsFields newSelect">
									<option data-type="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
									{foreach key=FIELD_TYPE item=FIELDS_INFO from=$LEADS_MODULE_MODEL->getFields()}
										{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
											<option data-type="{$FIELD_TYPE}" label="{\App\Language::translate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
												{\App\Language::translate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}
											</option>
										{/foreach}
									{/foreach}
								</select>
							</td>
							<td class="selectedFieldDataType textAlignCenter alignMiddle"></td>
							<td>
								<select class="accountsFields newSelect">
									<option data-type="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" label="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}" value="0">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
									{foreach key=FIELD_TYPE item=FIELDS_INFO from=$ACCOUNTS_MODULE_MODEL->getFields()}
										{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
											<option data-type="{$FIELD_TYPE}" label="{\App\Language::translate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
												{\App\Language::translate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
											</option>
										{/foreach}
									{/foreach}
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="row pushDown">
				<span class="col-md-4">
					<button id="addMapping" class="btn btn-info addButton" type="button">
						<span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('LBL_ADD_MAPPING', $QUALIFIED_MODULE)}</strong>
					</button>
				</span>
				<span class="col-md-8">
					<span class="float-right">
						<button type="submit" class="btn btn-success">
							<strong>
								<span class="fas fa-check mr-1"></span>
								{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
							</strong>
						</button>
						<button class="cancelLink btn btn-danger" type="reset" onclick="javascript:window.history.back();">
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</button>
					</span>
				</span>
			</div>
		</form>
	</div>
	<br />
{/strip}
