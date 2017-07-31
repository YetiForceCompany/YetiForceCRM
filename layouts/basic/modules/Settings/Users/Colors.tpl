{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
<div class="UserColors">
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{\App\Language::translate('LBL_COLORS_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>		
	</div>
	<div class="contents tabbable">
		<ul class="nav nav-tabs layoutTabs massEditTabs">
			<li class="active"><a data-toggle="tab" href="#userColors"><strong>{\App\Language::translate('LBL_USERS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#groupsColors"><strong>{\App\Language::translate('LBL_GROUPS_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#modulesColors"><strong>{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#marketing"><strong>{\App\Language::translate('LBL_MARKETING_PROCESSES', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#financial"><strong>{\App\Language::translate('LBL_SALES_PROCESSES', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#realization"><strong>{\App\Language::translate('LBL_REALIZATION_PROCESSES', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#support"><strong>{\App\Language::translate('LBL_SUPPORT_PROCESSES', $QUALIFIED_MODULE)}</strong></a></li>
			<li ><a data-toggle="tab" href="#timecontrol"><strong>{\App\Language::translate('LBL_TIMECONTROL_PROCESSES', $QUALIFIED_MODULE)}</strong></a></li>
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
							<tr data-id="{$item.id}" data-color="{$item.color}">
								<td>{$item.first}</td>
								<td>{$item.last}</td>
								<td class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateUserColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
									<button class="btn btn-sm btn-info generateColor" data-metod="generateUserColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
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
							<tr data-id="{$item.id}" data-color="{$item.color}">
								<td>{$item.groupname}</td>
								<td class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateGroupColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
									<button class="btn btn-sm btn-info generateColor" data-metod="generateGroupColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
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
									<input class="activeColor" type="checkbox" name="active" value="1" {if $item.active}checked=""{/if}>
								</td>
								<td class="calendarColor" style="background: {$item.color};"></td>
								<td>
									<button class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updateModuleColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
									<button class="btn btn-sm btn-info generateColor" data-metod="generateModuleColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{foreach from=$TABLES_ALL item=ELEMENTS key=PROCESS}
				<div class="tab-pane" id="{$PROCESS}">
					<div class="accordion">
						{foreach from=$ELEMENTS item=ITEM name=ELEMENT}
							{if $ITEM eq ''}
								{continue}
							{/if}
							<div class="accordion-group">
								<div class="accordion-heading">
									{assign var=TABLE value='vtiger_'|cat:$ITEM.fieldname}
									<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#{$TABLE}">
										{assign var=MODULE_NAME value=vtlib\Functions::getModuleName($ITEM.tabid)}
										{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
										:&ensp;
										{\App\Language::translate($ITEM.fieldlabel, $MODULE_NAME)}
									</a>
								</div>
								<div id="{$TABLE}" class="accordion-body collapse {if	$smarty.foreach.ELEMENT.index eq 0 } in {/if}">
									<div class="accordion-inner">
										<table class="table customTableRWD table-bordered table-condensed listViewEntriesTable" data-fieldname="{$ITEM.fieldname}">
											<thead>
												<tr class="blockHeader">
													<th><strong>{\App\Language::translate($ITEM.fieldlabel, $MODULE_NAME)}</strong></th>
													<th><strong>{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
													<th data-hide='phone'><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
												</tr>
											</thead>
											<tbody>
												{assign var=FIELD value=Users_Colors_Model::getValuesFromField($ITEM.fieldname)}
												{foreach from=$FIELD item=INNER_ITEM key=INNER_KEY}
													<tr data-table="{$TABLE}" data-id="{$INNER_ITEM['id']}" data-color="{$INNER_ITEM['color']}">
														<td>{\App\Language::translate($INNER_ITEM['value'], $MODULE_NAME)}</td>
														<td class="calendarColor" style="background: {$INNER_ITEM['color']};"></td>
														<td>
															<button class="btn btn-sm marginLeft10 btn-primary updateColor" data-metod="updateColorForProcesses">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
															<button class="btn btn-sm btn-info generateColor" data-metod="generateColorForProcesses">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
														</td>
													</tr>
												{/foreach}
											</tbody>
										</table>
									</div>
								</div>
							</div>
						{/foreach}
					</div>
				</div>
			{/foreach}
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
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
	</div>
</div>
{/strip}
