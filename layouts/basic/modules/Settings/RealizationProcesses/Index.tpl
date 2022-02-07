{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" supportProcessesContainer">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mt-2">
			<div class='editViewContainers' id="project_configuration">
				<table class="table tableRWD table-bordered table-sm themeTableColor userTable">
					<thead>
						<tr class="blockHeader">
							<th class="mediumWidthType">
								<span>{\App\Language::translate('LBL_INFO', $QUALIFIED_MODULE)}</span>
							</th>
							<th class="mediumWidthType">
								<span>{\App\Language::translate('LBL_TYPE', $QUALIFIED_MODULE)}</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="w-25"><label>{\App\Language::translate('LBL_PROJECT_STATUS_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>

								{assign var=STATUS_CLOSED_PROJECT value=$STATUS_NOT_MODIFY['Project']}
								<select class="select2 js-config-field" data-js="change" multiple name="projectStatus" data-moduleid="{$STATUS_CLOSED_PROJECT.id}">
									{foreach  item=STATUS from=\App\Fields\Picklist::getValuesName('projectstatus')}
										{if !empty($STATUS_NOT_MODIFY['Project'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_PROJECT.status)} selected {/if}>{\App\Language::translate($STATUS, 'Project')}</option>
										{else}
											<option value="{$STATUS}">{\App\Language::translate($STATUS, 'Project')}</option>
										{/if}
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="w-25">
								<label>{\App\Language::translate('LBL_PROJECTMILESTONE_STATUS_INFO', $QUALIFIED_MODULE)}</label>
							</td>
							<td>
								{assign var=STATUS_CLOSED_MILESTONE value=$STATUS_NOT_MODIFY['ProjectMilestone']}
								<select class="select2 js-config-field" data-js="change" multiple name="projectMilestoneStatus" data-moduleid="{$STATUS_CLOSED_MILESTONE.id}">
									{foreach  item=STATUS from=\App\Fields\Picklist::getValuesName('projectmilestone_status')}
										{if !empty($STATUS_NOT_MODIFY['ProjectMilestone'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_MILESTONE.status)} selected {/if}>{\App\Language::translate($STATUS, 'ProjectMilestone')}</option>
										{else}
											<option value="{$STATUS}">{\App\Language::translate($STATUS, 'ProjectMilestone')}</option>
										{/if}
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="w-25">
								<label>{\App\Language::translate('LBL_PROJECTTASK_STATUS_INFO', $QUALIFIED_MODULE)}</label>
							</td>
							<td>
								{assign var=STATUS_CLOSED_TASK value=$STATUS_NOT_MODIFY['ProjectTask']}
								<select class="select2 js-config-field" data-js="change" multiple name="projectTaskStatus" data-moduleid="{$STATUS_CLOSED_TASK.id}">
									{foreach  item=STATUS from=\App\Fields\Picklist::getValuesName('projecttaskstatus')}
										{if !empty($STATUS_NOT_MODIFY['ProjectTask'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_TASK.status)} selected {/if}>{\App\Language::translate($STATUS, 'ProjectTask')}</option>
										{else}
											<option value="{$STATUS}">{\App\Language::translate($STATUS, 'ProjectTask')}</option>
										{/if}
									{/foreach}
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}
