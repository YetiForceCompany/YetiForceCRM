{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header container-fluid openStreetMapModalHeader row">
		<div class="col col-md-5">
			<h4 id="massEditHeader" class="modal-title">{\App\Language::translate('LBL_MAP', $MODULE_NAME)}</h4>
		</div>
		<button type="button" class="close d-md-none" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<div class="col-md-6 ">
			<div class="input-group">
				<input type="text" class="searchValue form-control"
					   placeholder="{\App\Language::translate('LBL_SEARCH_VALUE_DESCRIPTION', $MODULE_NAME)}"/>
				<input type="text" class="form-control u-max-w-150px js-radius" data-js="val" size="6" placeholder="{\App\Language::translate('LBL_IN_RADIUS', $MODULE_NAME)}" />
				<div class="input-group-append">
					<button class="btn btn-primary input-group-btn searchBtn">{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)}</span></button>
				</div>
			</div>
		</div>
        <button type="button" class="close d-none d-md-block" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
        </button>
	</div>
	<div class="modal-body row openStreetMapModalBody">
		<div class="col-lg-9">
			<div id="mapid"></div>
		</div>
		<div class="col-lg-3">
			<div class="row">
				<div class="col-7 form-group">
					<div class="input-group">
						<input type="text" class="form-control searchCompany" />
						<span class="input-group-btn">
							<button class="btn btn-light addRecord" type="button">
								<span class="fas fa-plus"></span>
							</button>
						</span>
					</div>
				</div>
				<div class="col-5">
					<select class="select2 searchModule col-6">
						{foreach from=$ALLOWED_MODULES item=ALLOWED_MODULE_NAME}
							<option value="{$ALLOWED_MODULE_NAME}">{\App\Language::translate($ALLOWED_MODULE_NAME, $ALLOWED_MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="input-group mb-3">
				<div class="w-100">
					<select class="select2 form-control fieldsToGroup">
						<optgroup label="{\App\Language::translate($SRC_MODULE, $SRC_MODULE)}">
							{foreach from=$FIELDS_TO_GROUP item=FIELD_MODEL}
								<option value="{$FIELD_MODEL->getFieldName()}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SRC_MODULE)}</option>
							{/foreach}
						</optgroup>
					</select>
				</div>
				<div class="input-group-append">
					<span class="input-group-text">
						<input class="js-popover-tooltip groupNeighbours" data-js="popover" type="checkbox" checked="checked" data-content="{\App\Language::translate('LBL_GROUP_NEIGHBOURS', $MODULE_NAME)}" class="groupNeighbours" />
					</span>
					<button class="btn btn-primary groupBy">{\App\Language::translate('LBL_GROUP_BY', $MODULE_NAME)}</button>
				</div>
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header">
					{\App\Language::translate('LBL_CALCULATE_ROUTE_HEADER_BLOCK', $MODULE_NAME)}
				</div>
				<div class="card-body track">
					<div class="input-group group-btn input-group-sm form-group startContainer">
						<input type="text" readonly="readonly" class="form-control start" />
						<div class="input-group-btn">
							<button class="btn btn-success btn-sm setView">
								<span class="fas  fa-truck"></span>
							</button>
						</div>
					</div>
					<div class="input-group group-btn input-group-sm form-group indirectContainer indirectTemplate d-none">
						<input type="text" readonly="readonly" class="form-control indirect " />
						<div class="input-group-btn">
							<button class="btn btn-warning btn-sm setView">
								<span class="fas fa-flag"></span>
							</button>
							<button class="btn btn-success btn-sm moveUp">
								<span class="fas fa-upload"></span>
							</button>
							<button class="btn btn-success  btn-sm moveDown">
								<span class="fas fa-download"></span>
							</button>
							<button class="btn btn-danger btn-sm removeIndirect">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					<div class="input-group group-btn input-group-sm form-group">
						<input type="text" readonly="readonly" class="form-control end" />
						<div class="input-group-btn">
							<button class="btn btn-danger btn-sm setView">
								<span class="fas fa-flag-checkered"></span>
							</button>
						</div>
					</div>
					<div class="form-group float-right">
						<button class="btn btn-primary d-none calculateTrack">{\App\Language::translate('LBL_CALCULATE_TRACK', $MODULE_NAME)}</button>
					</div>
				</div>
			</div>
			<div class="card cacheContainer">
				<div class="card-header">
					{\App\Language::translate('LBL_CLIPBOARD', $MODULE_NAME)}
				</div>
				<div class="card-body cacheContent">
					{foreach from=$ALLOWED_MODULES item=ALLOWED_MODULE_NAME}
						<div class="cacheModuleContainer row mb-1">
							<div class="col-8">
								<label>
									<input type="checkbox" class="showRecordsFromCache" data-module="{$ALLOWED_MODULE_NAME}" />
									&nbsp;{\App\Language::translate($ALLOWED_MODULE_NAME, $ALLOWED_MODULE_NAME)}&nbsp;
									<span class="badge countRecords{$ALLOWED_MODULE_NAME}">
										{if !empty($CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME])}
											{$CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME]}
										{/if}
									</span>
								</label>
							</div>
							<div class="col-4">
								<button class="btn btn-sm btn-success addAllRecords float-right ml-1" data-module="{$ALLOWED_MODULE_NAME}"><span class="fas fa-download"></span></button>
								<button class="btn btn-sm btn-danger deleteClipBoard float-right {if empty($CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME])}d-none{/if}" data-module="{$ALLOWED_MODULE_NAME}"><span class="fas fa-trash-alt"></span></button>
							</div>
						</div>
					{/foreach}
					<button class="btn btn-success btn-sm copyToClipboard float-right"><span class="fas fa-paste"></span>&nbsp;{\App\Language::translate('LBL_COPY_TO_CLIPBOARD', $MODULE_NAME)}</button>
				</div>
			</div>
			<div class="card mt-3 d-none descriptionContainer">
				<div class="card-body descriptionContent">
					<b>{\App\Language::translate('LBL_DISTANCE', $MODULE_NAME)}:&nbsp;</b><span class="distance"></span><br />
					<b>{\App\Language::translate('LBL_TRAVEL_TIME', $MODULE_NAME)}:&nbsp;</b><span class="travelTime"></span><br />
					<b>{\App\Language::translate('LBL_INSTRUCTION', $MODULE_NAME)}:&nbsp;</b><span class="instruction"></span>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
	</div>
{/strip}
