{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($ALL_ACTIVEUSER_LIST)}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('Public')}
	{/if}
	{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE_NAME)->getAccessibleGroups('Public')}
	<div class="js-panel-item" data-js="container">
		<input type="hidden" id="{$MODULE_NAME}{$INDEX}" class="js-module-allocation-data" data-js="value"
			value="{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}" />
		<div class="card card-default js-panel" data-js="data/container" data-index="{$INDEX}"
			data-moduleid="{$MODULE_ID}" data-modulename="{$MODULE_NAME}">
			<div class="card-header">
				<div class="row">
					<div class="col-md-4 col-sm-5 col-5 form-control-plaintext">
						<h4 class="no-margin">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</h4>
					</div>
					<div class="float-right col-md-8 col-sm-7 col-7">
						<div class="row">
							<div class="col-10">
								<select id="userList{$INDEX}" class="select2 form-control js-base-user"
									data-js="change/value" data-validation-engine="validate[required]"
									data-placeholder="{\App\Language::translate('LBL_SELECT_USER')}"
									data-select="allowClear">
									<option value="">{\App\Language::translate('LBL_SELECT_USER')}</option>
									{foreach from=$ALL_ACTIVEUSER_LIST key=ID item=USER_NAME}
										<option value="{$ID}">{$USER_NAME}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-2">
								<div class="text-right">
									<button type="button" aria-label="{\App\Language::translate('LBL_REMOVE')}" title="{\App\Language::translate('LBL_REMOVE')}" class="js-remove-panel btn btn-danger" data-js="click"><span
											class="fas fa-trash-alt"></span></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card-body d-none js-panel-body" data-js="removeClass:d-none/append">
				<div class="js-clear-tables d-none row"
					data-js="removeClass:js-clear-tables,d-none/addClass:js-active-panel">
					<div class="col-12 col-sm-5">
						<div class="table-responsive">
							<div class="col-12">
								<table class="table table-bordered table-sm js-data-table" data-js="dataTable"
									data-mode="active">
									<thead>
										<tr>
											<th>
												<strong>{\App\Language::translate('LBL_USERS_AND_GROUPS',$QUALIFIED_MODULE)}</strong>
											</th>
										</tr>
									</thead>
									<tbody class="dropContainer">
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-2">
						<div class="text-center font-x-x-large betweenDragDrop">
							<span class="fas fa-arrows-alt-h"></span>
						</div>
					</div>
					<div class="col-12 col-sm-5">
						<div class="table-responsive">
							<div class="col-12">
								<table class="table table-bordered table-sm js-data-table" data-js="dataTable"
									data-mode="base">
									<thead>
										<tr>
											<th>
												<strong>{\App\Language::translate('LBL_USERS_AND_GROUPS',$QUALIFIED_MODULE)}</strong>
											</th>
										</tr>
									</thead>
									<tbody class="dropContainer">
										{foreach from=$ALL_ACTIVEUSER_LIST key=ID item=USER_NAME}
											<tr class="js-drag-drop-{$INDEX}" data-js="draggable/droppable" data-id="{$ID}"
												data-type="users">
												<td>{$USER_NAME}</td>
											</tr>
										{/foreach}
										{foreach from=$ALL_ACTIVEGROUP_LIST key=ID item=USER_NAME}
											<tr class="js-drag-drop-{$INDEX}" data-js="draggable/droppable" data-id="{$ID}"
												data-type="groups">
												<td>{\App\Language::translate($USER_NAME,$QUALIFIED_MODULE)}</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
