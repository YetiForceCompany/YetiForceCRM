{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
	<div class="">
		<div class="settingsHeader">
			<span class="col-md-8">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</span>
			<span class="col-md-4">
				<span class="pull-right">
					{foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
						<button type="button" class="btn btn-info" onclick={$LINK_MODEL->getUrl()}><strong>{vtranslate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}</strong></button>
					{/foreach}
				</span>
			</span>
		</div>
		<hr>
		<div class="contents" id="detailView">
			<table class="table table-bordered" width="100%">
				<tbody>
					<tr class="blockHeader">
						<th class="blockHeader" width="15%">{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
						<th class="blockHeader" width="15%">{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
						<th class="blockHeader textAlignCenter" colspan="3" width="70%">{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
					</tr>
					<tr>
						{foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
							<td width="15%"><b>{vtranslate($LABEL, $LABEL)}</b></td>
						{/foreach}
					</tr>
					{foreach key=MAPPING_ID item=MAPPING from=$MODULE_MODEL->getMapping()}
						<tr class="listViewEntries" data-cfmid="{$MAPPING_ID}">
							<td width="15%">{vtranslate({$MAPPING['Leads']['label']}, 'Leads')}</td>
							<td width="15%">{vtranslate($MAPPING['Leads']['fieldDataType'], $QUALIFIED_MODULE)}</td>
							<td width="13%">{vtranslate({$MAPPING['Accounts']['label']}, 'Accounts')}</td>
							<td width="13%">{vtranslate({$MAPPING['Contacts']['label']}, 'Contacts')}</td>
							<td width="13%">
								{vtranslate({$MAPPING['Potentials']['label']}, 'Potentials')}
								{if $MAPPING['editable'] eq 1}
									{foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
										<div class="pull-right actions">
											<span class="actionImages">
												<a onclick={$LINK_MODEL->getUrl()}><span title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
											</span>
										</div>
									{/foreach}
								{/if}
							</td>
						</tr>
						
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
