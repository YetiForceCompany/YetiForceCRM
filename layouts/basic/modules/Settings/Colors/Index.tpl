{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Colors-Index UserColors">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents tabbable mt-2">
			<ul class="nav nav-tabs layoutTabs massEditTabs">
				<li class="nav-item"><a class="nav-link" data-toggle="tab"
										href="#userColors"><strong>{\App\Language::translate('LBL_USERS_COLORS', $QUALIFIED_MODULE)}</strong></a>
				</li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab"
										href="#groupsColors"><strong>{\App\Language::translate('LBL_GROUPS_COLORS', $QUALIFIED_MODULE)}</strong></a>
				</li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab"
										href="#modulesColors"><strong>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}</strong></a>
				</li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#picklistsColors"
										id="picklistsColorsTab"><strong>{\App\Language::translate('LBL_PICKLISTS', $QUALIFIED_MODULE)}</strong></a>
				</li>
			</ul>
			<div class="tab-content layoutContent" style="padding-top: 10px;">
				<div class="tab-pane active" id="userColors">
					<table class="table customTableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('First Name',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('Last Name',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
							<th data-hide='phone'>
								<strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
						</tr>
						</thead>
						<tbody>
						{foreach from=\App\Colors::getAllUserColor() item=item key=key}
							<tr>
								<td>{$item.first}</td>
								<td>{$item.last}</td>
								<td id="calendarColorPreviewUser{$item.id}" data-color="{$item.color}"
									class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button data-record="{$item.id}"
											class="btn btn-sm btn-danger mr-1 float-right removeUserColor"><span
												class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-primary mr-1 float-right updateUserColor"><span
												class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-warning mr-1 float-right generateUserColor"><span
												class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="groupsColors">
					<table class="table customTableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('LBL_GROUP_NAME',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
							<th data-hide='phone'>
								<strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
						</tr>
						</thead>
						<tbody>
						{foreach from=\App\Colors::getAllGroupColor() item=item key=key}
							<tr>
								<td>{$item.groupname}</td>
								<td id="calendarColorPreviewGroup{$item.id}" data-color="{$item.color}"
									class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button data-record="{$item.id}"
											class="btn btn-sm btn-danger mr-1 float-right removeGroupColor"><span
												class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-primary mr-1 float-right updateGroupColor"><span
												class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-warning mr-1 float-right generateGroupColor"><span
												class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}
									</button>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="modulesColors">
					<table class="table customTableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
							<th data-hide='phone'>
								<strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
						</tr>
						</thead>
						<tbody>
						{foreach from=\App\Colors::getAllModuleColor() item=item key=key}
							<tr data-id="{$item.id}" data-color="{$item.color}">
								<td>{\App\Language::translate($item.module,$item.module)}</td>
								<td>
									<input data-record="{$item.id}" class="activeModuleColor" type="checkbox"
										   name="active" value="1" {if $item.active}checked=""{/if}>
								</td>
								<td id="calendarColorPreviewModule{$item.id}" data-color="{$item.color}"
									class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button data-record="{$item.id}"
											class="btn btn-sm btn-danger mr-1 float-right removeModuleColor"><span
												class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-primary mr-1 float-right updateModuleColor"><span
												class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}
									</button>&ensp;
									<button data-record="{$item.id}"
											class="btn btn-sm btn-warning mr-1 float-right generateModuleColor"><span
												class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}
									</button>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="picklistsColors">
					<div class="listViewContentDiv picklistViewContentDiv">

					</div>
				</div>

			</div>
		</div>
	</div>
{/strip}
