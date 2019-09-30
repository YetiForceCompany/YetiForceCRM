{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Dashboards-Updates -->
	<div class="tpl-Base-dashboards-Updates dashboardWidgetHeader">
		{function SHOW_SELECT_OWNER SELECT_FIELD_NAME='owner' SELECT_FIELD_LABEL='Assigned To'}
			<div class="input-group input-group-sm">
			<span class="input-group-prepend" title="{\App\Language::translate($SELECT_FIELD_LABEL, $MODULE_NAME)}">
				<span class="input-group-text">
					<span class="fas fa-user iconMiddle"></span>
				</span>
			</span>
			<select class="owner form-control" name="{$SELECT_FIELD_NAME}">
				{if in_array('mine', $AVAILABLE_OWNERS)}
					<option value="{$USER_MODEL->getId()}" data-name="{$USER_MODEL->getName()}"
							title="{\App\Language::translate('LBL_MINE')}">{\App\Language::translate('LBL_MINE')}</option>
				{/if}
				{if in_array('all', $AVAILABLE_OWNERS)}
					<option value="all" title="{\App\Language::translate('LBL_ALL')}">
						{\App\Language::translate('LBL_ALL')}
					</option>
				{/if}
				{assign var=ACCESSIBLE_USERS value=array_diff_key($ACCESSIBLE_USERS, array_flip([$USER_MODEL->getId()]))}
				{if !empty($ACCESSIBLE_USERS) && in_array('users', $AVAILABLE_OWNERS)}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_USERS}
							<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}"
									value="{$OWNER_ID}">{$OWNER_NAME}</option>
						{/foreach}
					</optgroup>
				{/if}
				{if !empty($ACCESSIBLE_GROUPS) && in_array('groups', $AVAILABLE_OWNERS)}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ACCESSIBLE_GROUPS}
							<option title="{$OWNER_NAME}" data-name="{$OWNER_NAME}"
									value="{$OWNER_ID}">{$OWNER_NAME}</option>
						{/foreach}
					</optgroup>
				{/if}
			</select>
		</div>
		{/function}
		<input type="hidden" class="js-widget-id" value="{$WIDGET->get('id')}" data-js="value">
		{assign var=MODAL_INDEX value="updatesWidget-{$WIDGET->get('id')}"}
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			<div class="flex-row">
			<div class="d-inline-flex">
			<button type="button" class="btn btn-sm btn-light js-update-widget-button" data-js="click">
					<span class="fas fa-cog" title="{\App\Language::translate('LBL_UPDATES_WIDGET_CONFIGURATION')}"></span>
			</button>
			</div>
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class=" input-group-prepend">
						<span class="input-group-text u-cursor-pointer">
							<span class="yfi-modules-widgets"></span>
						</span>
					</div>
					<select class="widgetFilter form-control select2" name="sourceModule"
							title="{\App\Language::translate('LBL_CUSTOM_FILTER')}" data-template-result="prependDataTemplate" data-template-selection="prependDataTemplate">
						{foreach key=MODULE_ID item=NAME from=$TRACKING_MODULES}
							<option value="{$MODULE_ID}"
								data-template="<span><span class='modCT_{$NAME} userIcon-{$NAME} mr-2'></span>{\App\Language::translate($NAME,$NAME)}</span>"
								{if $MODULE_ID eq $SELECTED_MODULE}selected="selected"{/if}>
								{\App\Language::translate($NAME, $NAME)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class=" input-group-prepend">
						<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
							<span class="fas fa-calendar-alt"	title="{\App\Language::translate('LBL_CHOOSE_DATE')}"></span>
						</span>
					</div>
					<input type="text" name="dateRange" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
							class="dateRangeField widgetFilter form-control textAlignCenter text-center"
							value="{implode(',', $DATE_RANGE)}" aria-label="{\App\Language::translate('LBL_CHOOSE_DATE')}" aria-describedby="inputGroup-sizing-sm"/>
				</div>
			</div>
		</div>
		<div class="modal fade js-update-widget-modal" tabindex="-1" aria-labelledby="{$MODAL_INDEX}" aria-hidden="true" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="{$MODAL_INDEX}">
							{\App\Language::translate('LBL_UPDATES_WIDGET_CONFIGURATION', $MODULE_NAME)}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
								aria-label="{\App\Language::translate('LBL_CANCEL')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-row alert alert-info p-1 mb-0 pb-2">
							{foreach key=VALUE item=TRACKER_ACTION from=ModTracker_Record_Model::$statusLabel}
								<div class="col-md-6">
									<div class="form-check">
										<input class="form-check-input js-tracker-action" type="checkbox" value="{$VALUE}" data-js="container">
										<label class="form-check-label">
											{\App\Language::translate($TRACKER_ACTION, 'ModTracker')|ucfirst}
										</label>
									</div>
								</div>
							{/foreach}
						</div>
						<div class="row">
							<div class="col-md-6 mt-1">
								{SHOW_SELECT_OWNER}
							</div>
							<div class="col-md-6 mt-1">
								{SHOW_SELECT_OWNER SELECT_FIELD_NAME="historyOwner" SELECT_FIELD_LABEL='LBL_PERSON_MAKING_CHANGES'}
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="js-modal__save btn btn-success btn-sm" type="submit" name="saveButton" data-js="click">
								<strong>{\App\Language::translate("LBL_SAVE", $MODULE_NAME)}</strong>
						</button>
						<button class="btn btn-danger btn-sm" type="reset" data-dismiss="modal">
							<span class="fas fa-times mr-1"></span>
							<strong>{\App\Language::translate("LBL_CANCEL", $MODULE_NAME)}</strong>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/UpdatesContents.tpl', $MODULE_NAME)}
	</div>
<!-- /tpl-Base-Dashboards-Updates -->
{/strip}
