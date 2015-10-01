{*<!--
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
-->*}
<div id="activityStateModal" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}</h3>
			</div>
			{assign var=ID value=$RECORD->get('id')}
			{assign var=ACTIVITYPOSTPONED value=isPermitted('Calendar', 'ActivityPostponed', $ID)}
			{assign var=ACTIVITYCANCEL value=isPermitted('Calendar', 'ActivityCancel', $ID)}
			{assign var=ACTIVITYCOMPLETE value=isPermitted('Calendar', 'ActivityComplete', $ID)}
			{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
			{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
			{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
			<div class="modal-body">
				<div class="textAlignCenter">
					{if $ACTIVITYCANCEL eq 'yes' && $EMPTY}
						<button type="button" class="btn btn-danger" data-state='{$ACTIVITY_STATE_LABEL.cancelled}' data-id='{$ID}'>{vtranslate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}</button>
					{/if}
					{if $ACTIVITYCOMPLETE eq 'yes' && $EMPTY}
						<button type="button" class="btn btn-success" data-state='{$ACTIVITY_STATE_LABEL.completed}' data-id='{$ID}'>{vtranslate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}</button>
					{/if}
					{if $ACTIVITYPOSTPONED eq 'yes' && $EMPTY}
						<button type="button" class="btn btn-primary showQuickCreate" data-state='{$ACTIVITY_STATE_LABEL.postponed}' data-id='{$ID}'>{vtranslate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}</button>
					{/if}
					{if !$EMPTY}
						{vtranslate('LBL_NO_AVAILABLE_ACTIONS', $MODULE_NAME)}
					{/if}
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn btn-warning" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</a>
			</div>      
		</div>
	</div>
</div>
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
{/foreach}	
