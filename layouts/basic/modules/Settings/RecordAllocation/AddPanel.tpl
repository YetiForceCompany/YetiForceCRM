{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($ALL_ACTIVEUSER_LIST)}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('Public')}
	{/if}
	{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE_NAME)->getAccessibleGroups('Public')}
	<div class="panelItem">
		<input type="hidden" id="{$MODULE_NAME}{$INDEX}" class="moduleAllocationData" value="{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}" />
		<div class="panel panel-default" data-index="{$INDEX}" data-moduleid="{$MODULE_ID}" data-modulename="{$MODULE_NAME}">
			<div class="panel-heading">
				<div class="row">
					<div class="col-md-4 col-sm-5 col-5 form-control-plaintext">
						<h4 class="no-margin">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</h4>
					</div>
					<div class="float-right col-md-4 col-sm-7 col-7">
						<div class="row">
							<div class="col-10">
								<select id="userList{$INDEX}" class="chzn-select form-control baseUser" data-validation-engine="validate[required]">
									<option value=""></option>
									{foreach from=$ALL_ACTIVEUSER_LIST key=ID item=USER_NAME}
										<option value="{$ID}">{$USER_NAME}</option>
									{/foreach}
								</select>
							</div>
							<div class="col-2 paddingLefttZero">
								<div class="float-right">
									<button type="button" class="removePanel btn btn-danger"><span class="fas fa-trash-alt"></span></button>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>		
			<div class="panel-body padding5 hide">
				<div class="clearTables hide">
					<div class="col-12 col-sm-5 paddingLRZero">
						<div class="table-responsive">
							<div class="col-12">
								<table class="table table-bordered table-sm dataTable" data-mode="active">
									<thead>
										<tr>
											<th><strong>{\App\Language::translate('LBL_USERS_AND_GROUPS',$QUALIFIED_MODULE)}</strong></th>
										</tr>
									</thead>
									<tbody class="dropContainer">
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-2">
						<div class="textAlignCenter font-x-x-large betweenDragDrop">
							<span class="fas fa-arrows-alt-h"></span>
						</div>
					</div>
					<div class="col-12 col-sm-5 paddingLRZero">
						<div class="table-responsive">
							<div class="col-12">
								<table class="table table-bordered table-sm dataTable" data-mode="base">
									<thead>
										<tr>
											<th><strong>{\App\Language::translate('LBL_USERS_AND_GROUPS',$QUALIFIED_MODULE)}</strong></th>
										</tr>
									</thead>
									<tbody class="dropContainer">
										{foreach from=$ALL_ACTIVEUSER_LIST key=ID item=USER_NAME}
											<tr class="dragDrop{$INDEX}" data-id="{$ID}" data-type="users">
												<td>{$USER_NAME}</td>
											</tr>
										{/foreach}
										{foreach from=$ALL_ACTIVEGROUP_LIST key=ID item=USER_NAME}
											<tr class="dragDrop{$INDEX}" data-id="{$ID}" data-type="groups">
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
