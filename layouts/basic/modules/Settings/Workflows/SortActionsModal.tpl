{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-SortActionsModal -->
	<div class="modal-body">
		{if $WORKFLOW_ACTIONS}
			<div class="form-group">
				{App\Language::translate('LBL_SELECT_WORKFLOW', $QUALIFIED_MODULE)}<br />
				<select class="select2 form-control js-workflow-for-sort" data-js="value">
					{foreach key=WORKFLOW_ID item=ACTION from=$WORKFLOW_ACTIONS}
						<option value="{$WORKFLOW_ID}">{$ACTION['summary']}</option>
					{/foreach}
				</select>
			</div>
			<div class="form-group">
				{App\Language::translate('LBL_SET_WORKFLOW_BEFORE', $QUALIFIED_MODULE)}<br />
				<select class="select2 form-control js-workflow-before" data-js="value">
					{foreach key=WORKFLOW_ID item=ACTION from=$WORKFLOW_ACTIONS}
						<option value="{$WORKFLOW_ID}">{$ACTION['summary']}</option>
					{/foreach}
				</select>
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-Workflows-SortActionsModal -->
{/strip}
