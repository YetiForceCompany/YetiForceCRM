{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-HelpDesk-Hierarchy modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="modCT_{$MODULE_NAME} yfm-{$MODULE_NAME} mr-2"></span>
						{\App\Language::translate('LBL_SHOW_HIERARCHY', $MODULE_NAME)}
					</h5>
					<button type="button" class="close" data-dismiss="modal"
						title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="hierarchyScroll">
						<table class="table table-bordered">
							<thead>
								<tr class="blockHeader">
									{foreach item=HEADERNAME from=$HIERARCHY['header']}
										<th class="text-center text-nowrap">{\App\Language::translate($HEADERNAME, $MODULE_NAME)}</th>
									{/foreach}
								</tr>
							</thead>
							{foreach item=ENTRIES from=$HIERARCHY['entries']}
								<tbody>
									<tr>
										{foreach item=LISTFIELDS from=$ENTRIES}
											<td class="text-nowrap">{$LISTFIELDS}</td>
										{/foreach}
									</tr>
								</tbody>
							{/foreach}
						</table>
					</div>
					<div class="c-panel c-panel--edit">
						<div class="blockHeader c-panel__header align-items-center">
							<h5 class="mb-0 ml-2">{\App\Language::translate('LBL_MASS_STATUS_CHANGE', $MODULE_NAME)}</h5>
						</div>
						<div class="row pt-2 pb-2">
							<div class="col-6">
								<label class="my-0 fieldLabel text-lg-left text-xl-right u-text-small-bold" for="status">{\App\Language::translate('LBL_STATUS', $MODULE_NAME)}</label>
								<select id="status" class="select2 js-status">
									{foreach key=VALUE item=STATUS from=$STATUS_PICKLIST}
										<option value="{$VALUE}">{\App\Language::translate({$STATUS}, $MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-6">
								<label class="my-0 fieldLabel text-lg-left text-xl-right u-text-small-bold" for="status">{\App\Language::translate('LBL_RECORDS', $MODULE_NAME)}</label>
								<select class="select2 js-selected-records">
									<option value="all">{\App\Language::translate('LBL_ALL')}</option>
									<option value="child">{\App\Language::translate('LBL_CHILD_RECORDS', $MODULE_NAME)}</option>
								</select>
							</div>
						</div>
						<div class="row pb-2">
							<div class="col-12">
								<div class="float-right cancelLinkContainer">
									<button class="btn btn-success js-update-hierarchy" data-js="click">
										<strong>{\App\Language::translate('LBL_UPDATE', $MODULE_NAME)}</strong>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer">
						<button class="btn btn-warning" type="reset" data-dismiss="modal">
							<strong>{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
