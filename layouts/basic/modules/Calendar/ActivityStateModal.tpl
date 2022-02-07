{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="activityStateModal" class="js-activity-state modal fade modalEditStatus" tabindex="-1">
		{assign var=ID value=$RECORD->getId()}
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><span class="fas fa-question-circle mr-1"></span>
						{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}
					</h5>
					<div class="">
						{foreach item=LINK from=$LINKS}
							{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewBasic' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
						{/foreach}
						<button type="button" class="close" data-dismiss="modal"
							aria-label="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
						</button>
					</div>
				</div>
				<div class="modal-body">
					{include file=\App\Layout::getTemplatePath('ActivityStateContent.tpl', $MODULE_NAME)}
				</div>
				<div class="modal-footer">
					<div class="col-12 p-0">
						{if $RECORD->isEditable()}
							{assign var=ACTIVITY_STATE_LABEL value=Calendar_Module_Model::getComponentActivityStateLabel()}
							{assign var=ACTIVITY_STATE value=$RECORD->get('activitystatus')}
							{assign var=EMPTY value=!in_array($ACTIVITY_STATE, [$ACTIVITY_STATE_LABEL.cancelled,$ACTIVITY_STATE_LABEL.completed])}
							<div class="float-left js-activity-buttons" data-js="container">
								{assign var=SHOW_QUICK_CREATE value=App\Config::module('Calendar','SHOW_QUICK_CREATE_BY_STATUS')}
								{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityCancel', $ID)}
									<button type="button"
										class="mr-1 btn btn-warning {if in_array($ACTIVITY_STATE_LABEL.cancelled,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
										data-state="{$ACTIVITY_STATE_LABEL.cancelled}" data-id="{$ID}"
										data-type="1">
										<span class="fas fa-ban mr-1"></span>
										{\App\Language::translate($ACTIVITY_STATE_LABEL.cancelled, $MODULE_NAME)}
									</button>
								{/if}
								{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityComplete', $ID)}
									<button type="button"
										class="mr-1 btn c-btn-done {if in_array($ACTIVITY_STATE_LABEL.completed,$SHOW_QUICK_CREATE)}showQuickCreate{/if}"
										data-state="{$ACTIVITY_STATE_LABEL.completed}" data-id="{$ID}"
										data-type="1">
										<span class="far fa-check-square fa-lg mr-1"></span>
										{\App\Language::translate($ACTIVITY_STATE_LABEL.completed, $MODULE_NAME)}
									</button>
								{/if}
								{if $EMPTY && \App\Privilege::isPermitted($MODULE_NAME, 'ActivityPostponed', $ID)}
									<button type="button" class="mr-1 btn btn-primary showQuickCreate"
										data-state="{$ACTIVITY_STATE_LABEL.postponed}" data-id="{$ID}"
										data-type="0">
										<span class="fas fa-angle-double-right mr-1"></span>
										{\App\Language::translate($ACTIVITY_STATE_LABEL.postponed, $MODULE_NAME)}
									</button>
								{/if}
								{if !$EMPTY}
									{\App\Language::translate('LBL_NO_AVAILABLE_ACTIONS', $MODULE_NAME)}
								{/if}
							</div>
						{/if}
						<div class="float-right">
							<a href="#" class="btn btn-danger" role="button" data-dismiss="modal">
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
{/strip}
