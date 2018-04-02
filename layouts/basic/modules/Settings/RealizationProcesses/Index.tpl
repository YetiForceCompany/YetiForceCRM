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
								{assign var=STATUS_CLOSED value=$STATUS_NOT_MODIFY['Project']}
								<select class="select2 js-config-field" data-js="change" multiple name="projectStatus" data-moduleid="{$STATUS_CLOSED.id}">
									{foreach  item=STATUS from=$PROJECT_STATUS}
										<option value="{$STATUS}" {if in_array($STATUS, $STATUS_CLOSED.status)} selected {/if}  >{\App\Language::translate($STATUS, 'Project')}</option>
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
