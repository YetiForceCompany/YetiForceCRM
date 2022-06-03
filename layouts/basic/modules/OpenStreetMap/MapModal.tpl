{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OpenStreet-MapModal -->
	<div class="modal-body row pt-2 openStreetMapModalBody">
		<input type="hidden" class="js-tile-layer-server" value="{\App\Map\Layer::getTileServer()}" data-js="val">
		<div class="col-lg-9 pr-0">
			<div id="mapid"></div>
		</div>
		<div class="col-lg-3">
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header blockHeader p-2 font-weight-bold">
					<span class="fas fa-search mr-2"></span>
					{\App\Language::translate('LBL_SEARCH_COMPANY_INPUT', $MODULE_NAME)}
					<div class="js-popover-tooltip u-cursor-pointer ml-2" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate('LBL_SEARCH_COMPANY_DESCRIPTION', $MODULE_NAME))}">
						<span class="fas fa-info-circle"></span>
					</div>
					<div class="ml-auto">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					</div>
				</div>
				<div class="card-body blockContent input-group p-2">
					<input type="text" class="form-control js-search-company" placeholder="{\App\Language::translate('LBL_TYPE_SEARCH', $MODULE_NAME)}" />
					<div class="input-group-btn">
						<select class="select2 searchModule col-6">
							{foreach from=$ALLOWED_MODULES item=ALLOWED_MODULE_NAME}
								<option value="{$ALLOWED_MODULE_NAME}">{\App\Language::translate($ALLOWED_MODULE_NAME, $ALLOWED_MODULE_NAME)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header blockHeader p-2 font-weight-bold">
					<span class="fas fa-palette mr-2"></span>
					{\App\Language::translate('LBL_GROUP_POINTS', $MODULE_NAME)}
					<div class="js-popover-tooltip u-cursor-pointer ml-2" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate('LBL_GROUP_POINTS_DESC', $MODULE_NAME))}">
						<span class="fas fa-info-circle"></span>
					</div>
					<div class="ml-auto">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					</div>
				</div>
				<div class="card-body blockContent input-group p-2">
					<div class="input-group-prepend col p-0">
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
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header blockHeader p-2 font-weight-bold">
					<span class="fas fa-route mr-2"></span>
					{\App\Language::translate('LBL_CALCULATE_ROUTE_HEADER_BLOCK', $MODULE_NAME)}
					<div class="ml-auto">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					</div>
				</div>
				<div class="card-body blockContent track p-2">
					<div class="input-group group-btn input-group-sm form-group mb-2 startContainer">
						<input type="text" readonly="readonly" class="form-control start" />
						<div class="input-group-btn">
							<button class="btn btn-success btn-sm setView"><span class="fas fa-truck"></span></button>
						</div>
					</div>
					<div class="input-group group-btn input-group-sm form-group mb-2 indirectContainer indirectTemplate d-none">
						<input type="text" readonly="readonly" class="form-control indirect " />
						<div class="input-group-btn">
							<button class="btn btn-warning btn-sm setView"><span class="fas fa-flag"></span></button>
							<button class="btn btn-success btn-sm moveUp"><span class="fas fa-upload"></span></button>
							<button class="btn btn-success  btn-sm moveDown"><span class="fas fa-download"></span></button>
							<button class="btn btn-danger btn-sm removeIndirect"><span class="fas fa-times"></span></button>
						</div>
					</div>
					<div class="input-group group-btn input-group-sm form-group mb-0">
						<input type="text" readonly="readonly" class="form-control end" />
						<div class="input-group-btn">
							<button class="btn btn-danger btn-sm setView"><span class="fas fa-flag-checkered"></span></button>
						</div>
					</div>
					<div class="float-right mt-2 d-none">
						<button class="btn btn-primary btn-sm js-calculate-route">
							<span class="fas fa-road mr-2"></span>{\App\Language::translate('LBL_CALCULATE_TRACK', $MODULE_NAME)}
						</button>
					</div>
				</div>
			</div>
			<div class="js-toggle-panel c-panel d-none  js-description-container" data-js="click">
				<div class="card-header blockHeader p-2 font-weight-bold">
					<span class="fas fa-route mr-2"></span>
					{\App\Language::translate('LBL_ROUTE_DESCRIPTION', $MODULE_NAME)}
					<div class="ml-auto">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					</div>
				</div>
				<div class="card-body blockContent descriptionContainer p-2">
					<b>{\App\Language::translate('LBL_DISTANCE', $MODULE_NAME)}:&nbsp;</b><span class="distance"></span><br />
					<b>{\App\Language::translate('LBL_TRAVEL_TIME', $MODULE_NAME)}:&nbsp;</b><span class="travelTime"></span><br />
					<b class="js-instruction_block">{\App\Language::translate('LBL_INSTRUCTION', $MODULE_NAME)}:&nbsp;</b><br />
					<span class="js-instruction_body"></span>
				</div>
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header blockHeader p-2 font-weight-bold">
					<span class="fas fa-download mr-2"></span>
					{\App\Language::translate('LBL_CLIPBOARD', $MODULE_NAME)}
					<div class="js-popover-tooltip u-cursor-pointer ml-2" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate('LBL_CLIPBOARD_DESC', $MODULE_NAME))}">
						<span class="fas fa-info-circle"></span>
					</div>
					<div class="ml-auto">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>
					</div>
				</div>
				<div class="card-body blockContent cacheContent p-2">
					{foreach from=$ALLOWED_MODULES item=ALLOWED_MODULE_NAME}
						<div class="cacheModuleContainer row mb-1">
							<div class="col-8">
								<label>
									<input type="checkbox" class="showRecordsFromCache mr-2" data-module="{$ALLOWED_MODULE_NAME}" />
									{\App\Language::translate($ALLOWED_MODULE_NAME, $ALLOWED_MODULE_NAME)}
									<span class="badge badge-info badge-pill ml-2 countRecords{$ALLOWED_MODULE_NAME}">
										{if !empty($CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME])}
											{$CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME]}
										{/if}
									</span>
								</label>
							</div>
							<div class="col-4">
								<button class="btn btn-sm btn-success addAllRecords js-popover-tooltip float-right ml-2" data-module="{$ALLOWED_MODULE_NAME}" data-content="{\App\Purifier::encodeHtml(App\Language::translate('BTN_LOAD_ALL_CLIPBOARD', $MODULE_NAME))}">
									<span class="fas fa-download"></span>
								</button>
								<button class="btn btn-sm btn-danger js-delete-clip-board js-popover-tooltip float-right {if empty($CACHE_GROUP_RECORDS[$ALLOWED_MODULE_NAME])}d-none{/if}" data-module="{$ALLOWED_MODULE_NAME}" data-content="{\App\Purifier::encodeHtml(App\Language::translate('BTN_DELETE_CLIPBOARD', $MODULE_NAME))}">
									<span class="fas fa-trash-alt"></span>
								</button>
							</div>
						</div>
					{/foreach}
					<button class="btn btn-success btn-sm copyToClipboard float-right"><span class="fas fa-paste"></span>&nbsp;{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}</button>
				</div>
			</div>
		</div>
		<div class="js-legend-container" data-js="html"></div>
	</div>
	<!-- /tpl-OpenStreet-MapModal -->
{/strip}
