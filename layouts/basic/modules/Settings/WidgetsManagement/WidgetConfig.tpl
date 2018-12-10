{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WidgetsManagement-WidgetConfig -->
	{assign var=WIDGET_INFO value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('data')))}
	{assign var=LINKID value=$WIDGET_MODEL->get('linkid')}
	{assign var=LINK_LABEL_KEY value=$WIDGET_MODEL->get('linklabel')}
	<li class="col-md-12">
		<div class="opacity editFieldsWidget ml-0 border1px" data-block-id="{$AUTHORIZATION_KEY}"
			 data-field-id="{$WIDGET_MODEL->get('id')}" data-linkid="{$LINKID}" data-sequence="">
			<div class="row p-2 d-flex justify-content-between">
				<div style="word-wrap: break-word;">
					<span class="fieldLabel ml-3">{\App\Language::translate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</span>
				</div>
				<span class="btn-group mr-3 actions">
					<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
						<span class="fas fa-edit alignMiddle"
							  title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
					</a>
					<div class="basicFieldOperations d-none u-overflow-x-hidden pl-2 pr-2" style="width: 375px;">
						<form class="form-horizontal fieldDetailsForm" method="POST">
							<input type="hidden" name="type" class="" value="{$LINK_LABEL_KEY}">
							<div class="modal-header">
								<h5 class="modal-title">{\App\Language::translate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="clearfix">
								<div class="row pt-2">
									<div class="col-md-5 col-form-label text-left">
										{\App\Language::translate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 text-right checkboxForm">
										<input type="checkbox" name="isdefault"
											   class="" {if $WIDGET_MODEL->get('isdefault') eq 1} checked {/if}>
									</div>
								</div>
								<div class="row pt-2">
									<div class="col-md-5 col-form-label text-left">
										{\App\Language::translate('LBL_CACHE_WIDGET', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 text-right checkboxForm">
										<input type="checkbox" name="cache"
											   class="" {if $WIDGET_MODEL->get('cache') eq 1} checked {/if}>
									</div>
								</div>
								{assign var=WIDGET_SIZE value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('size')))}
								<div class="row pt-2">
									<div class="col-md-5 col-form-label text-left">
										{\App\Language::translate('LBL_WIDTH', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 text-right">
										<select class="form-control" name="width">
											{foreach from=$SIZE.width item=item}
												<option value="{$item}" {if $WIDGET_SIZE.width eq $item} selected {/if}>{$item}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="row pt-2">
									<div class="col-md-5 col-form-label text-left">
										{\App\Language::translate('LBL_HEIGHT', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 text-center">
										<select class="form-control" name="height">
											{foreach from=$SIZE.height item=item}
												<option value="{$item}" {if $WIDGET_SIZE.height eq $item} selected {/if}>{$item}</option>
											{/foreach}
										</select>
									</div>
								</div>
								{if in_array($LINK_LABEL_KEY, $TITLE_OF_LIMIT) }
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7 text-center">
											<input type="text" name="limit" class="form-control"
												   value="{$WIDGET_MODEL->get('limit')}">
										</div>
									</div>
								{/if}
								{if $LINK_LABEL_KEY === 'DW_SUMMATION_BY_MONTHS' }
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_TICK_SIZE', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7 text-center">
											<input type="text" name="plotTickSize" class="form-control"
												   value="{$WIDGET_INFO['plotTickSize']}">
										</div>
									</div>
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_MAXIMUM_VALUE', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7 text-center">
											<input type="text" name="plotLimit" class="form-control"
												   value="{$WIDGET_INFO['plotLimit']}">
										</div>
									</div>
								{/if}
								{if $LINK_LABEL_KEY === 'DW_SUMMATION_BY_USER'}
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_SHOW_USERS', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7 text-center checkboxForm">
											<input type="checkbox" name="showUsers"
													{if $WIDGET_INFO['showUsers'] eq 1} checked {/if}>
										</div>
									</div>
								{/if}
							</div>
							{if in_array($LINK_LABEL_KEY,$WIDGETS_WITH_FILTER_USERS)}
								<div>
									{assign var=WIDGET_OWNERS value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('owners')))}
									{if isset($RESTRICT_FILTER[$LINK_LABEL_KEY]) && is_array($RESTRICT_FILTER[$LINK_LABEL_KEY])}
										{assign var=RESTRICT_FILTER_FOR_LABEL value=$RESTRICT_FILTER[$LINK_LABEL_KEY]}
									{else}
										{assign var=RESTRICT_FILTER_FOR_LABEL value=[]}
									{/if}
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7">
											<select class="widgetFilter form-control" id="owner"
													name="default_owner">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
													{if !in_array($OWNER_ID, $RESTRICT_FILTER_FOR_LABEL) }
														<option value="{$OWNER_ID}" {if $WIDGET_OWNERS.default eq $OWNER_ID} selected {/if} >{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
													{/if}
												{/foreach}
											</select>
										</div>
									</div>
									{if !is_array($WIDGET_OWNERS.available)}
										{$WIDGET_OWNERS.available = array($WIDGET_OWNERS.available)}
									{/if}
									<div class="row pt-2">
										<div class="col-md-5 col-form-label text-left">
											{\App\Language::translate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
										</div>
										<div class="col-md-7">
											<select class="widgetFilter form-control" multiple="true"
													name="owners_all"
													placeholder="{\App\Language::translate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">

												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
													{if !in_array($OWNER_ID, $RESTRICT_FILTER_FOR_LABEL) }
														<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $WIDGET_OWNERS.available)} selected {/if} >													{\App\Language::translate($OWNER_NAME, $QUALIFIED_MODULE)}
														</option>
													{/if}
												{/foreach}
											</select>
										</div>
									</div>
								</div>
							{/if}
							{if $LINK_LABEL_KEY === 'Calendar'}
								<div class="row pt-2">
									<div class="col-md-5 col-form-label text-left">
										{\App\Language::translate('LBL_DEFAULT_LIST_FILTER', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 controls">
										<select class="widgetFilter form-control" name="defaultFilter">
											{assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup('Calendar')}
											{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
												<optgroup
														label='{\App\Language::translate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}'>
													{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
														{if !(\App\Privilege::isPermitted({$CUSTOM_VIEW->module->name}))}
															{continue}
														{/if}
														{if $CUSTOM_VIEW->get('setmetrics') eq 1}
															<option title="{\App\Language::translate($CUSTOM_VIEW->module->name)}"
																	data-module="{$CUSTOM_VIEW->module->name}"
																	value="{$CUSTOM_VIEW->get('cvid')}"
																	{if !empty($WIDGET_INFO['defaultFilter']) && $CUSTOM_VIEW->get('cvid') eq $WIDGET_INFO['defaultFilter']}
																		selected="selected"
																	{/if}
															>
																{$CUSTOM_VIEW->getOwnerName()}
																- {\App\Language::translate($CUSTOM_VIEW->get('viewname'), $CUSTOM_VIEW->module->name)}
															</option>
														{/if}
													{/foreach}
												</optgroup>
											{/foreach}
										</select>
									</div>
								</div>
							{/if}
							{if $LINK_LABEL_KEY === 'Multifilter'}
								<div class="row pt-2">
									<div class="col-sm-5 col-form-label">
										{\App\Language::translate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-7 controls">
										<select class="widgetFilter form-control" name="customMultiFilter"
												multiple="multiple">
											{assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAll()}
											{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
												{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
													{if !(\App\Privilege::isPermitted({$GROUP_CUSTOM_VIEWS->module->name}))}
														{continue}
													{/if}
													<option title="{\App\Language::translate($GROUP_CUSTOM_VIEWS->module->name)}"
															data-module="{$GROUP_CUSTOM_VIEWS->module->name}"
															value="{$GROUP_CUSTOM_VIEWS->get('cvid')}"
															{if !empty($WIDGET_INFO['customMultiFilter']) && in_array($GROUP_CUSTOM_VIEWS->get('cvid'),$WIDGET_INFO['customMultiFilter'])}
																selected="selected"
															{/if}
													>
														{\App\Language::translate($GROUP_CUSTOM_VIEWS->module->name,$GROUP_CUSTOM_VIEWS->module->name)}
														- {\App\Language::translate($GROUP_CUSTOM_VIEWS->get('viewname'), $GROUP_CUSTOM_VIEWS->module->name)}
													</option>
												{/foreach}
											{/foreach}
										</select>
									</div>
								</div>
							{/if}
							{if in_array($LINK_LABEL_KEY, $WIDGETS_WITH_FILTER_DATE)}
								<div class="form-group ">
									<div class="col-sm-5 col-form-label">
										{\App\Language::translate('LBL_DEFAULT_DATE', $QUALIFIED_MODULE)}
									</div>
									<div class="col-sm-7 controls">
										<select class="widgetFilterDate form-control" id="date"
												name="default_date">
											{foreach key=DATE_VALUE item=DATE_TEXT from=$DATE_SELECT_DEFAULT}
												<option value="{$DATE_VALUE}" {if $DATE_VALUE eq $WIDGET_MODEL->get('date')} selected {/if}>{\App\Language::translate($DATE_TEXT, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
							{/if}
							<div class="modal-footer">
								<button class="btn btn-success saveFieldDetails"
										data-field-id="{$WIDGET_MODEL->get('id')}" type="submit">
									<strong>
										<span class="fas fa-check mr-1"></span>
										{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
									</strong>
								</button>
								<button class='cancel btn btn-danger' type="reset">
									<span class="fas fa-times mr-1"></span>
									{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
								</button>
							</div>
						</form>
					</div>&nbsp;
					<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$WIDGET_MODEL->get('id')}">
						<span class="fas fa-trash-alt alignMiddle"
							  title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
					</a>
				</span>
			</div>
		</div>
	</li>
	<!-- /tpl-Settings-WidgetsManagement-WidgetConfig -->
{/strip}
