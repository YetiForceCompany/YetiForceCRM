{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class=" supportProcessesContainer">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
		</div>
		<ul id="tabs" class="nav nav-tabs mt-1" data-tabs="tabs">
			<li class="nav-item"><a class="nav-link active" href="#general_configuration" data-toggle="tab">{\App\Language::translate('LBL_GENERAL_CONFIGURATION', $QUALIFIED_MODULE)} </a></li>
		</ul>
		<div class="tab-content">
			<div class="editViewContainer tab-pane active" id="general_configuration">
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
						<tr data-id="{$ITEM['user_id']}">
							<td class="w-25"><label>{\App\Language::translate('LBL_TICKET_STATUS_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								<select class="select2 js-config-field form-control status" data-js="change" multiple name="status">
									{foreach  item=STATUS from=$TICKETSTATUS}
										<option value="{$STATUS}" {if in_array($STATUS, $TICKETSTATUSNOTMODIFY)} selected {/if}  >{\App\Language::translate($STATUS, 'HelpDesk')}</option>
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
