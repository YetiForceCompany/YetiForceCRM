{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header container-fluid">
		<div class="col-xs-5">
			<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_MAP', $MODULE_NAME)}</h3>
		</div>
		<div class="col-xs-6">
			<div class="input-group group-btn form-group">
				<input type="text" class="searchValue form-control" placeholder="{vtranslate('LBL_SEARCH_VALUE_DESCRIPTION', $MODULE_NAME)}">
				<span class="input-group-btn" style="width:0px;"></span>
				<input type="text" class="form-control radius" placeholder="{vtranslate('LBL_IN_RADIUS', $MODULE_NAME)}">
				<div class="input-group-btn">
					<button class="btn btn-primary searchBtn">{vtranslate('LBL_SEARCH', $MODULE_NAME)}</button>
				</div>
			</div>
		</div>
		<div class="col-xs-1">
			<button type="button" class="btn btn-warning pull-right marginLeft10" data-dismiss="modal" aria-hidden="true">&times;</button>
		</div>
	</div>
	<div class="modal-body container-fluid openStreetMapModalBody">
		<div class="col-xs-9 paddingLRZero">
			<div id="mapid"></div>
		</div>
		<div class="col-xs-3">
			<div class="input-group group-btn form-group">
				<select class="select2 fieldsToGroup">
					{foreach from=$FIELDS_TO_GROUP item=FIELD_MODEL}
						<option value="{$FIELD_MODEL->getFieldName()}">{vtranslate($FIELD_MODEL->getFieldLabel(), $SRC_MODULE)}</option>
					{/foreach}
				</select>
				<span class="input-group-addon">
					<input class="popoverTooltip" type="checkbox" checked="checked" data-content="{vtranslate('LBL_GROUP_NEIGHBOURS', $MODULE_NAME)}" class="groupNeighbours">
				</span>
				<div class="input-group-btn">
					<button class="btn btn-primary groupBy">{vtranslate('LBL_GROUP_BY', $MODULE_NAME)}</button>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					{vtranslate('LBL_CALCULATE_ROUTE_HEADER_BLOCK', $MODULE_NAME)}
				</div>
				<div class="panel-body track">
					<div class="input-group group-btn form-group">
						<input type="text" readonly="readonly" class="form-control start">
						<div class="input-group-btn">
							<button class="btn btn-success">
								<span class="fa fa-truck"></span>
							</button>
						</div>
					</div>
					<div class="input-group group-btn form-group">
						<input type="text" readonly="readonly" class="form-control end">
						<div class="input-group-btn">
							<button class="btn btn-danger">
								<span class="fa fa-flag-checkered"></span>
						</div>
					</div>
					<div class="form-group pull-right">
						<button class="btn btn-primary calculateTrack">{vtranslate('LBL_CALCULATE_TRACK', $MODULE_NAME)}</button>
					</div>
				</div>
			</div>
			<div class="panel panel-default hide descriptionContainer">
				<div class="panel-body descriptionContent">
					<b>{vtranslate('LBL_DISTANCE', $MODULE_NAME)}:&nbsp</b><span class="distance"></span><br>
					<b>{vtranslate('LBL_TRAVEL_TIME', $MODULE_NAME)}:&nbsp</b><span class="travelTime"></span><br>
					<b>{vtranslate('LBL_INSTRUCTION', $MODULE_NAME)}:&nbsp</b><span class="instruction"></span>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
	</div>
{/strip}
