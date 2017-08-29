{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} --!>*}
{strip}
	<br />
	<div class="row">
		<label class="fieldLabel col-md-2"><strong>{\App\Language::translate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
		<div class="col-md-4 fieldValue pickListModulesSelectContainer">
			<select class="chzn-select form-control" id="pickListModules">
				<optgroup>
					<option value="">{\App\Language::translate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
					{foreach key=PICKLIST_MODULE_ID item=PICKLIST_MODULE from=\App\Fields\Picklist::getModules()}
						<option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE.tabname} selected="" {/if} value="{$PICKLIST_MODULE.tabname}">{\App\Language::translate($PICKLIST_MODULE.tablabel,$PICKLIST_MODULE.tabname)}</option>
					{/foreach}
				</optgroup>
			</select>
		</div>
		{if $SELECTED_MODULE_NAME}
			<label class="fieldLabel col-md-2"><strong>{\App\Language::translate('LBL_SELECT_PICKLIST',$QUALIFIED_MODULE)}</strong></label>
			<div class="col-md-4 fieldValue pickListModulesPicklistSelectContainer">
				<select class="chzn-select form-control" id="modulePickList">
					<optgroup>
						{foreach key=PICKLIST_FIELD item=FIELD_MODEL from=$PICKLIST_FIELDS}
							<option {if $SELECTED_PICKLIST_FIELD_ID eq $FIELD_MODEL->getId()} selected="" {/if}  value="{$FIELD_MODEL->getId()}">{\App\Language::translate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
		{/if}
	</div>
	<br />
	<div class="row">
		{if $SELECTED_PICKLIST_FIELDMODEL}
			<div class="col-md-12 marginLeftZero textOverflowEllipsis">
				{if $COLOR_NO_COLUMN}
					<div class="alert alert-warning" role="alert">
						<strong>{\App\Language::translate('LBL_WARNING',$QUALIFIED_MODULE)}!</strong> {\App\Language::translate('LBL_PICKLIST_COLOR_COL_NOT_FOUND',$QUALIFIED_MODULE)}
						<br>
						<br>
						<button data-picklistModule="{$SELECTED_MODULE_NAME}" data-picklistId="{$SELECTED_PICKLIST_FIELD_ID}" data-type="PicklistColumn" class="btn btn-sm btn-primary marginLeft10 addColorColumn" data-metod="addPicklistColorColumn">{\App\Language::translate('LBL_UPDATE_COLOR_COLUMN',$QUALIFIED_MODULE)}</button>
					</div>
				{else}
					<table id="pickListValuesTable" class="table table-bordered">
						<thead>
							<tr class="listViewHeaders">
								<th class="col-lg-6">{\App\Language::translate('LBL_ITEM',$QUALIFIED_MODULE)}</th>
								<th class="col-lg-2">{\App\Language::translate('LBL_COLOR',$QUALIFIED_MODULE)}</th>
								<th class="col-lg-4">{\App\Language::translate('LBL_ACTIONS',$QUALIFIED_MODULE)}</th>
							</tr>
						</thead>
						<tbody>
							{assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
							{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
								<tr class="pickListValue">
									<td class="textOverflowEllipsis">{\App\Language::translate($PICKLIST_VALUE.picklistValue,$SELECTED_MODULE_NAME)}</td>
									<td id="calendarColorPreviewPicklistItem{$PICKLIST_VALUE.picklist_valueid}" data-picklistId="{$SELECTED_PICKLIST_FIELD_ID}" data-picklistValueId="{$PICKLIST_VALUE.picklist_valueid}" data-color="{$PICKLIST_VALUE.color}" class="calendarColor" style="background: #{$PICKLIST_VALUE.color} !important;"></td>
									<td>
										<button data-picklistId="{$SELECTED_PICKLIST_FIELD_ID}" data-picklistValueId="{$PICKLIST_VALUE.picklist_valueid}" data-type="PicklistItem" class="btn btn-sm btn-primary marginLeft10 updateColor" data-metod="updatePicklistItemColor">{\App\Language::translate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
										<button data-picklistId="{$SELECTED_PICKLIST_FIELD_ID}" data-picklistValueId="{$PICKLIST_VALUE.picklist_valueid}" data-type="PicklistItem" class="btn btn-sm btn-info generateColor" data-metod="generatePicklistItemColor">{\App\Language::translate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				{/if}
			</div>
		{/if}
	</div>
{/strip}
