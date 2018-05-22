{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" supportProcessesContainer">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-1" data-tabs="tabs">
			<li class="nav-item"><a class="nav-link active" href="#project_configuration" data-toggle="tab">{\App\Language::translate('LBL_PROJECT', $QUALIFIED_MODULE)} </a></li>
		</ul>
		<div class="tab-content">
			<div class='editViewContainer tab-pane active' id="project_configuration">
				<table class="table tableRWD table-bordered table-sm themeTableColor userTable">
					<thead>
						<tr class="blockHeader" >
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
									{foreach  item=STATUS from=$PROJECT_STATUS['Project']}
										{if !empty($STATUS_NOT_MODIFY['Project'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_PROJECT.status)} selected {/if}  >{\App\Language::translate($STATUS, 'Project')}</option>
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
									{foreach  item=STATUS from=$PROJECT_STATUS['ProjectMilestone']}
										{if !empty($STATUS_NOT_MODIFY['ProjectMilestone'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_MILESTONE.status)} selected {/if} >{\App\Language::translate($STATUS, 'ProjectMilestone')}</option>
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
									{foreach  item=STATUS from=$PROJECT_STATUS['ProjectTask']}
										{if !empty($STATUS_NOT_MODIFY['ProjectTask'])}
											<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED_TASK.status)} selected {/if} >{\App\Language::translate($STATUS, 'ProjectTask')}</option>
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
