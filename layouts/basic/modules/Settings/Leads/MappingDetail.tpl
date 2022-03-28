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
	<div class="tpl-Settings-Leads-MappingDetail row align-items-center widget_header">
		<div class="col-md-8">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
		<div class="col-md-4 btn-toolbar ml-0 justify-content-end">
			<div>
				{foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
					<button type="button" class="btn btn-info" onclick={$LINK_MODEL->getUrl()}>
						<strong>
							<span class="yfi yfi-full-editing-view u-mr-5px"></span>{\App\Language::translate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}
						</strong>
					</button>
				{/foreach}
			</div>
		</div>
	</div>
	<div class='clearfix'></div>
	<div class=" contents" id="detailView">
		<table class="table customTableRWD table-bordered my-2" id="convertLeadMapping">
			<thead>
				<tr class="blockHeader">
					<th class="blockHeader">{\App\Language::translate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
					<th class="blockHeader">{\App\Language::translate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
					<th data-hide='phone'
						class="blockHeader">{\App\Language::translate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
				</tr>
			</thead>
		</table>
		<table class="table customTableRWD table-bordered" id="convertLeadMapping">
			<thead>
				<tr>
					{foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders() name=index}
						<th {if $smarty.foreach.index.iteration > 2}data-hide='phone' {/if}>
							<b>{\App\Language::translate($LABEL, $LABEL)}</b>
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=MAPPING_ID item=MAPPING from=$MODULE_MODEL->getMapping()}
					<tr class="listViewEntries" data-cfmid="{$MAPPING_ID}">
						<td>{\App\Language::translate({$MAPPING['Leads']['label']}, 'Leads')}</td>
						<td>{\App\Language::translate($MAPPING['Leads']['fieldDataType'], $QUALIFIED_MODULE)}</td>
						<td>{if isset($MAPPING['Accounts']['label'])}{\App\Language::translate($MAPPING['Accounts']['label'], 'Accounts')}{/if}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{/strip}
