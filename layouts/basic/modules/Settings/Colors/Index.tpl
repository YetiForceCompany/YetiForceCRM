{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Colors-Index UserColors">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_COLORS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents tabbable">
			<ul class="nav nav-tabs layoutTabs massEditTabs">
				<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#calendarColors"><strong>{\App\Language::translate('LBL_CALENDAR_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#userColors"><strong>{\App\Language::translate('LBL_USERS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#groupsColors"><strong>{\App\Language::translate('LBL_GROUPS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#modulesColors"><strong>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="nav-item" ><a class="nav-link" data-toggle="tab" href="#picklistsColors" id="picklistsColorsTab"><strong>{\App\Language::translate('LBL_PICKLISTS', $QUALIFIED_MODULE)}</strong></a></li>
			</ul>
			<div class="tab-content layoutContent" style="padding-top: 10px;">
				<div class="tab-pane active" id="calendarColors">
					<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('LBL_CALENDAR_TYPE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\Settings_Calendar_Module_Model::getCalendarConfig('colors') item=item key=key}
								<tr data-id="{$item.name}" data-color="{$item.value}" data-type="config">
									<td>{\App\Language::translate($item.label,$QUALIFIED_MODULE)}</td>
									<td id="calendarColorPreviewCalendar{$item.name}"  class="calendarColor" style="background: {$item.value};"></td>
									<td >
										<button data-record="{$item.name}" class="btn btn-sm btn-danger removeCalendarColor mr-1 float-right"><span class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.name}" class="btn btn-sm btn-primary updateColor mr-1 float-right" data-metod="UpdateCalendarConfig"><span class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.name}" class="btn btn-sm btn-warning generateColor mr-1 float-right"><span class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="userColors">
					<table class="table customTableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('First Name',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('Last Name',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Colors::getAllUserColor() item=item key=key}
								<tr>
									<td>{$item.first}</td>
									<td>{$item.last}</td>
									<td id="calendarColorPreviewUser{$item.id}" data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-record="{$item.id}" class="btn btn-sm btn-danger mr-1 float-right removeUserColor"><span class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-primary mr-1 float-right updateUserColor"><span class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-warning mr-1 float-right generateUserColor"><span class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
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
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Colors::getAllGroupColor() item=item key=key}
								<tr>
									<td>{$item.groupname}</td>
									<td id="calendarColorPreviewGroup{$item.id}" data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-record="{$item.id}" class="btn btn-sm btn-danger mr-1 float-right removeGroupColor"><span class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-primary mr-1 float-right updateGroupColor"><span class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-warning mr-1 float-right generateGroupColor"><span class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="modulesColors">
					<table  class="table customTableRWD table-bordered table-sm listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=\App\Colors::getAllModuleColor() item=item key=key}
								<tr data-id="{$item.id}" data-color="{$item.color}">
									<td>{\App\Language::translate($item.module,$item.module)}</td>
									<td>
										<input data-record="{$item.id}" class="activeModuleColor" type="checkbox" name="active" value="1" {if $item.active}checked=""{/if}>
									</td>
									<td id="calendarColorPreviewModule{$item.id}" data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-record="{$item.id}" class="btn btn-sm btn-danger mr-1 float-right removeModuleColor"><span class="fas fa-trash-alt"></span> {\App\Language::translate('LBL_REMOVE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-primary mr-1 float-right updateModuleColor"><span class="fas fa-edit"></span> {\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-record="{$item.id}" class="btn btn-sm btn-warning mr-1 float-right generateModuleColor"><span class="fas fa-redo-alt"></span> {\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
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
		<div class="modal editColorContainer fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<h5 class="modal-title">{\App\Language::translate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form class="form-horizontal">
							<input type="hidden" class="selectedColor" value="" />
							<div class="form-group form-row">
								<label class=" col-sm-4 col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
								<div class=" col-sm-8 controls">
									<p class="calendarColorPicker"></p>
								</div>
							</div>
						</form>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</div>
			</div>
		</div>
	</div>
{/strip}
