{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header container-fluid">
		<div class="col-xs-6">
			<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_MAP', $MODULE_NAME)}</h3>
		</div>
		<div class="col-xs-6 ">
			<button type="button" class="btn btn-warning pull-right marginLeft10" data-dismiss="modal" aria-hidden="true">&times;</button>
			<div class="pull-right">
				<div class="checkbox">
					<label>
						<input type="checkbox" checked="checked" class="groupNeighbours">
						{vtranslate('LBL_GROUP_NEIGHBOURS', $MODULE_NAME)}
					</label>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-body container-fluid">
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
				<div class="input-group-btn">
					<button class="btn btn-primary groupBy">{vtranslate('LBL_GROUP_BY', $MODULE_NAME)}</button>
				</div>
			</div>
			<div class="form-group">
				<input type="text" class="searchValue form-control" placeholder="{vtranslate('LBL_SEARCH_VALUE_DESCRIPTION', $MODULE_NAME)}">
			</div>
			<div class="form-group">
				{vtranslate('LBL_IN_RADIUS', $MODULE_NAME)}
				<input type="text" class="form-control radius" placeholder="{vtranslate('LBL_IN_RADIUS', $MODULE_NAME)}">
			</div>
			<div class="form-group pull-right">
				<button class="btn btn-primary searchBtn">{vtranslate('LBL_SEARCH', $MODULE_NAME)}</button>
			</div>
			<div class="track pull-left">
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
			</div>
			<div class="form-group pull-right">
				<button class="btn btn-primary calculateTrack">{vtranslate('LBL_CALCULATE_TRACK', $MODULE_NAME)}</button>
			</div>
		</div>
	</div>
	<div class="modal-footer">
	</div>
{/strip}
