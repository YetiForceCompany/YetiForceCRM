{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row marginBottom10px">
		<div class="col-md-12 btn-toolbar"">
			<div class="pull-right btn-group">
				<input class="switchBtn switchBtnReload recentActivitiesSwitch" type="checkbox" {if $TYPE eq 'changes'}checked=""{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_UPDATES', $MODULE_BASE_NAME)}" data-off-text="{vtranslate('LBL_REVIEW_HISTORY', $MODULE_BASE_NAME)}" data-urlparams="whereCondition" data-on-val="changes" data-off-val="review">
			</div>
			{if $USER_MODEL->getId() eq $USER_MODEL->getRealId() && $MODULE_MODEL->isPermitted('ReviewingUpdates') && ModTracker_Record_Model::isNewChange($PARENT_RACORD_ID, $USER_MODEL->getRealId())}
				<div class="pull-right btn-group">
					<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn" title="{vtranslate('BTN_CHANGES_REVIEWED_ON', $MODULE_BASE_NAME)}">
						<span class="glyphicon glyphicon-ok-circle"></span>
					</button>
				</div>
			{/if}
		</div>
	</div>
{/strip}
