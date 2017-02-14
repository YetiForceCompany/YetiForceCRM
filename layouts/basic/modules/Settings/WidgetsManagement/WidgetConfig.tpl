{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=WIDGET_INFO value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('data')))}
	{assign var=LINKID value=$WIDGET_MODEL->get('linkid')}
	<li class="col-md-12">
		<div class="opacity editFieldsWidget marginLeftZero border1px" data-block-id="{$AUTHORIZATION_KEY}" data-field-id="{$WIDGET_MODEL->get('id')}" data-linkid="{$LINKID}" data-sequence="">
			<div class="row padding1per">
				<div class="pull-left " style="word-wrap: break-word;">
					<span class="fieldLabel marginLeft20">{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</span>
				</div>
				<span class="btn-group pull-right marginRight20 actions">
					<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
						<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
					</a>
					<div class="basicFieldOperations hide pull-right" style="width : 375px;">
						<form class="form-horizontal fieldDetailsForm" method="POST">
							<input type="hidden" name="type" class="" value="{$WIDGET_MODEL->get('linklabel')}">
							<div class="modal-header contentsBackground">
								<strong>{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</strong>
								<div class="pull-right"><a href="javascript:void(0)" class='cancel'>X</a></div>
							</div>
							<div class="clearfix">
								<div class="row">
									<div class="col-md-3 text-center checkboxForm">
										<input type="checkbox" name="isdefault" class="" {if $WIDGET_MODEL->get('isdefault') eq 1} checked {/if}>
									</div>	
									<label class="col-md-9 form-control-static pull-left" >
										&nbsp;&nbsp;{vtranslate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}&nbsp;
									</label>
								</div>
								<div class="row">
									<div class="col-md-3 text-center checkboxForm">
										<input type="checkbox" name="cache" class="" {if $WIDGET_MODEL->get('cache') eq 1} checked {/if}>
									</div>	
									<label class="col-md-9 form-control-static pull-left" >
										&nbsp;&nbsp;{vtranslate('LBL_CACHE_WIDGET', $QUALIFIED_MODULE)}&nbsp;
									</label>
								</div>
								{assign var=WIDGET_SIZE value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('size')))}
								<div class="row padding1per">
									<div class="col-md-3 text-center">
										<select class="width col-md-1 pull-left form-control" name="width" >
											{foreach from=$SIZE.width item=item}
												<option value="{$item}" {if $WIDGET_SIZE.width eq $item} selected {/if}>{$item}</option>
											{/foreach}
										</select>
									</div>	
									<label  class="col-md-9 marginTop5 pull-left" >
										&nbsp;{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}&nbsp;
									</label>
								</div>
								<div class="row padding1per">
									<div class="col-md-3 text-center">
										<select class="height col-md-1 pull-left form-control" name="height">
											{foreach from=$SIZE.height item=item}
												<option value="{$item}" {if $WIDGET_SIZE.height eq $item} selected {/if}>{$item}</option>
											{/foreach}
										</select>
									</div>
									<label class="col-md-9 marginTop5 pull-left" >
										&nbsp;{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}&nbsp;
									</label>	
								</div>
								{if in_array($WIDGET_MODEL->get('linklabel'), $TITLE_OF_LIMIT) }
									<div class="row padding1per">
										<div class="col-md-3 text-center">
											<input type="text" name="limit" class="col-md-1 form-control" value="{$WIDGET_MODEL->get('limit')}" >
										</div>
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
								{/if}
								{if $WIDGET_MODEL->get('linklabel') == 'DW_SUMMATION_BY_MONTHS' }
									<div class="row padding1per">
										<div class="col-md-3 text-center">
											<input type="text" name="plotTickSize" class="col-md-1 form-control" value="{$WIDGET_INFO['plotTickSize']}" >
										</div>
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_TICK_SIZE', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
									<div class="row padding1per">
										<div class="col-md-3 text-center">
											<input type="text" name="plotLimit" class="col-md-1 form-control" value="{$WIDGET_INFO['plotLimit']}" >
										</div>
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_MAXIMUM_VALUE', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
								{/if}
								{if $WIDGET_MODEL->get('linklabel') == 'DW_SUMMATION_BY_USER'}
									<div class="row padding1per">
										<div class="col-md-3 text-center checkboxForm">
											<input type="checkbox" name="showUsers" class="" {if $WIDGET_INFO['showUsers'] eq 1} checked {/if}>
										</div>	
										<label class="col-md-9 form-control-static pull-left" >
											&nbsp;&nbsp;{vtranslate('LBL_SHOW_USERS', $QUALIFIED_MODULE)}
										</label>
									</div>
								{/if}
							</div>
							{if in_array($WIDGET_MODEL->get('linklabel'),$WIDGETS_WITH_FILTER_USERS)}
								<div class="">
									{assign var=WIDGET_OWNERS value=\App\Json::decode(html_entity_decode($WIDGET_MODEL->get('owners')))}
									<div class="row padding1per">
										<div class="col-md-5">
											<select class="widgetFilter form-control" id="owner" name="default_owner">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
													{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
														<option value="{$OWNER_ID}" {if $WIDGET_OWNERS.default eq $OWNER_ID} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
													{/if}
												{/foreach}
											</select>
										</div>
										<label class="col-md-6 form-control-static" >
											{vtranslate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
										</label>
									</div>
									{if !is_array($WIDGET_OWNERS.available)}
										{$WIDGET_OWNERS.available = array($WIDGET_OWNERS.available)}
									{/if}
									<div class="row padding1per">
										<div class="col-md-8">
											<select class="widgetFilter form-control" multiple="true" name="owners_all" placeholder="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
													{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
														<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $WIDGET_OWNERS.available)} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
													{/if}
												{/foreach}
											</select>
										</div>
										<label class="col-md-3 form-control-static" >
											{vtranslate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
										</label>
									</div>	
								</div>
							{/if}
							{if in_array($WIDGET_MODEL->get('linklabel'), $WIDGETS_WITH_FILTER_DATE)}
								<div class="form-group ">
									<div class="col-sm-3 control-label">
										{vtranslate('LBL_DEFAULT_DATE', $QUALIFIED_MODULE)}
									</div>
									<div class="col-sm-8 controls">
										<select class="widgetFilterDate form-control" id="date" name="default_date">
											{foreach key=DATE_VALUE item=DATE_TEXT from=$DATE_SELECT_DEFAULT}
												<option value="{$DATE_VALUE}" {if $DATE_VALUE eq $WIDGET_MODEL->get('date')} selected {/if}>{vtranslate($DATE_TEXT, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>	
								</div>
							{/if}
							<div class="modal-footer">
								<span class="pull-right">
									<div class="pull-right"><button class='cancel btn btn-warning' type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button></div>
									<button class="btn btn-success saveFieldDetails" data-field-id="{$WIDGET_MODEL->get('id')}" type="submit">
										<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
									</button>
								</span>
							</div>
						</form>
					</div>&nbsp;
					<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$WIDGET_MODEL->get('id')}">
						<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
					</a>
				</span>
			</div>
		</div>
	</li>
{/strip}
