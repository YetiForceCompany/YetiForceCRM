{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
<div class=" UserColors">
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_CALENDAR_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>		
	</div>
	<hr>
	<div class="">
        <div class="contents tabbable">
            <ul class="nav nav-tabs layoutTabs massEditTabs">
               	<li class="active"><a data-toggle="tab" href="#calendarColors"><strong>{vtranslate('LBL_CALENDAR_COLORS', $QUALIFIED_MODULE)}</strong></a></li>
				<li><a data-toggle="tab" href="#calendarConfig"><strong>{vtranslate('LBL_CALENDAR_CONFIG', $QUALIFIED_MODULE)}</strong></a></li>
				<li><a data-toggle="tab" href="#workingDays"><strong>{vtranslate('LBL_NOTWORKING_DAYS', $QUALIFIED_MODULE)}</strong></a></li>
            </ul>
			<div class="tab-content layoutContent" style="padding-top: 10px;">
				<div class="tab-pane active" id="calendarColors">
					<table class="table tableRWD table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_CALENDAR_TYPE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$MODULE_MODEL->getCalendarConfig('colors') item=item key=key}
								<tr data-id="{$item.name}" data-color="{$item.value}" data-table="{$item.table}" data-field="{$item.field}">
									<td>{vtranslate($item.label,$QUALIFIED_MODULE)}</td>
									<td class="calendarColor" style="background: {$item.value};"></td>
									<td>
										<button class="btn btn-primary marginLeftZero updateColor" data-metod="UpdateCalendarConfig">{vtranslate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button class="btn btn-info generateColor">{vtranslate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane paddingTop20" id="calendarConfig">
					<table class="table table-bordered table-condensed listViewEntriesTable">
						<tbody>
							{foreach from=$MODULE_MODEL->getCalendarConfig('reminder') item=item key=key}
								<tr data-id="{$item.name}" data-color="{$item.value}">
									<td class="col-md-3"><p class="paddingTop10">{vtranslate($item.label,$QUALIFIED_MODULE)}</p></td>
									<td class="col-md-9">
										<input class="marginTop10" type="checkbox" id="update_event" name="update_event" data-metod="UpdateCalendarConfig" value=1 {if $item.value eq 1} checked{/if}/>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane paddingTop20" id="workingDays">
					<table class="table table-bordered table-condensed listViewEntriesTable workingDaysTable">	
						<tbody>
							<tr>
								<td class="col-md-3"><p style="padding-top:10px;">{vtranslate('LBL_NOTWORKEDDAYS_INFO', $QUALIFIED_MODULE)}</p></td>
								<td class="col-md-9">
									<div class="col-md-4">
										<select class="chzn-select workignDaysField pull-left" multiple id="update_workingdays" name="notworkingdays" data-metod="updateNotWorkingDays">
											<option value="1" {if in_array(1, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_MONDAY,$QUALIFIED_MODULE)}</option>
											<option value="2" {if in_array(2, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_TUESDAY,$QUALIFIED_MODULE)}</option>
											<option value="3" {if in_array(3, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_WEDNESDAY,$QUALIFIED_MODULE)}</option>
											<option value="4" {if in_array(4, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_THURSDAY,$QUALIFIED_MODULE)}</option>
											<option value="5" {if in_array(5, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_FRIDAY,$QUALIFIED_MODULE)}</option>
											<option value="6" {if in_array(6, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_SATURDAY,$QUALIFIED_MODULE)}</option>
											<option value="7" {if in_array(7, $NOTWORKINGDAYS)} selected {/if} >{vtranslate(PLL_SUNDAY,$QUALIFIED_MODULE)}</option>
										</select>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="modal editColorContainer fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<input type="hidden" class="selectedColor" value="" />
						<div class="form-group">
							<label class=" col-sm-3 control-label">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
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
{/strip}
