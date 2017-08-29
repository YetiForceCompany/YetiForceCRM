{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="UserColors">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_COLORS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div class="contents tabbable">
			<ul class="nav nav-tabs layoutTabs massEditTabs">
				<li class="active"><a data-toggle="tab" href="#userColors"><strong>{\App\Language::translate('LBL_USERS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li ><a data-toggle="tab" href="#groupsColors"><strong>{\App\Language::translate('LBL_GROUPS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li ><a data-toggle="tab" href="#modulesColors"><strong>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}</strong></a></li>
				<li ><a data-toggle="tab" href="#picklistsColors" id="picklistsColorsTab"><strong>{\App\Language::translate('LBL_PICKLISTS', $QUALIFIED_MODULE)}</strong></a></li>
			</ul>
			<div class="tab-content layoutContent" style="padding-top: 10px;">
				<div class="tab-pane active" id="userColors">
					<table class="table customTableRWD table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('First Name',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('Last Name',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=Users_Colors_Model::getUserColors() item=item key=key}
								<tr>
									<td>{$item.first}</td>
									<td>{$item.last}</td>
									<td id="calendarColorPreviewUser{$item.id}" data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-id="{$item.id}" data-type="User" class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateUserColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-id="{$item.id}" data-type="User" class="btn btn-sm btn-info generateColor" data-metod="generateUserColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="groupsColors">
					<table class="table customTableRWD table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('LBL_GROUP_NAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=Users_Colors_Model::getGroupColors() item=item key=key}
								<tr>
									<td>{$item.groupname}</td>
									<td id="calendarColorPreviewGroup{$item.id}"data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-id="{$item.id}" data-type="Group" class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateGroupColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-id="{$item.id}" data-type="Group" class="btn btn-sm btn-info generateColor" data-metod="generateGroupColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="modulesColors">
					<table  class="table customTableRWD table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{\App\Language::translate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=Users_Colors_Model::getModulesColors() item=item key=key}
								<tr data-id="{$item.id}" data-color="{$item.color}">
									<td>{\App\Language::translate($item.module,$item.module)}</td>
									<td>
										<input data-id="{$item.id}" data-type="Module" class="activeColor" type="checkbox" name="active" value="1" {if $item.active}checked=""{/if}>
									</td>
									<td id="calendarColorPreviewModule{$item.id}" data-color="{$item.color}" class="calendarColor" style="background: {$item.color};"></td>
									<td>
										<button data-id="{$item.id}" data-type="Module" class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateModuleColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-id="{$item.id}" data-type="Module" class="btn btn-sm btn-info generateColor" data-metod="generateModuleColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="picklistsColors">
					<div class="listViewContentDiv" id="PicklistViewContentDiv">

					</div>
				</div>

			</div>
		</div>
		<div class="modal editColorContainer fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 class="modal-title">{\App\Language::translate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h3>
					</div>
					<div class="modal-body">
						<form class="form-horizontal">
							<input type="hidden" class="selectedColor" value="" />
							<div class="form-group">
								<label class=" col-sm-3 control-label">{\App\Language::translate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
								<div class=" col-sm-8 controls">
									<p class="calendarColorPicker"></p>
								</div>
							</div>
						</form>
					</div>
					{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $MODULE)}
				</div>
			</div>
		</div>
	</div>
{/strip}
