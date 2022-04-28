{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Calendar-ActivityButtons -->
	{if !empty($RECORD)}
		<div class="js-activity-buttons d-flex justify-content-center flex-wrap mb-2" data-js="container">
			{assign var=ID value=$RECORD->getId()}
			{if $RECORD->isEditable()}
				{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
				{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
				{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
				{assign var=SHOW_QUICK_CREATE value=App\Config::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityCancel', $ID)}
					<button type="button"
						class="mr-1 mt-1 btn btn-sm btn-warning {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
						data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}"
						data-type="1" data-js="click"
						title="{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}">
						<span class="fas fa-ban"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</span>
					</button>
				{/if}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityComplete', $ID)}
					<button type="button"
						class="mr-1 mt-1 btn btn-sm c-btn-done {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
						data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}"
						data-type="1" data-js="click"
						title="{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}">
						<span class="far fa-check-square fa-lg"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</span>
					</button>
				{/if}
				{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityPostponed', $ID)}
					<button type="button" class="mr-1 mt-1 btn btn-sm btn-primary showQuickCreate"
						data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}"
						data-type="0"
						data-dismiss="modal"
						data-js="click"
						title="{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}">
						<span class="fas fa-angle-double-right"></span>
						<span class="ml-1">{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}</span>
					</button>
				{/if}
			{/if}
		</div>
	{/if}
	<!-- /tpl-Calendar-Calendar-ActivityButtons -->
{/strip}
