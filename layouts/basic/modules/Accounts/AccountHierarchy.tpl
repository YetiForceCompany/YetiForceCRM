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
	<div class="tpl-AccountHierarchy modelContainer modal fade" id="accountHierarchyContainer" tabindex="-1" role="dialog">
		<div class="modal-dialog c-modal-xxl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-sitemap u-mr-5px"></span>
						{\App\Language::translate('LBL_SHOW_ACCOUNT_HIERARCHY', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
					</button>
				</div>
				<div class="modal-body maxHeightModal">
					<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
								<tr class="blockHeader">
									{foreach item=HEADERNAME from=$ACCOUNT_HIERARCHY['header']}
										<th>{\App\Language::translate($HEADERNAME, $MODULE)}</th>
									{/foreach}
								</tr>
							</thead>
							<tbody>
								{foreach key=RECORD_ID item=ENTRIES from=$ACCOUNT_HIERARCHY['entries'] name=hierarchyEntries}
									<tr {if $smarty.foreach.hierarchyEntries.first} class="bgAzure" {/if}
										data-id="{$RECORD_ID}">
										{foreach item=LISTFIELDS from=$ENTRIES}
											<td>
												{$LISTFIELDS['data']}
											</td>
										{/foreach}
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer">
						<button class="btn btn-warning" type="reset" data-dismiss="modal">
							<strong>{\App\Language::translate('LBL_CLOSE', $MODULE)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
