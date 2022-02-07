{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-RecentActivitiesHeader row marginBottom10px">
		<div class="col-md-12 btn-toolbar justify-content-end">
			{if $USER_MODEL->getId() eq $USER_MODEL->getRealId() && $MODULE_MODEL->isPermitted('ReviewingUpdates') && ModTracker_Record_Model::isNewChange($PARENT_RACORD_ID, $USER_MODEL->getRealId())}
				<button id="btnChangesReviewedOn" type="button" class="btn btn-success btn-sm btnChangesReviewedOn mr-1"
					title="{\App\Language::translate('BTN_CHANGES_REVIEWED_ON', $MODULE_BASE_NAME)}">
					<span class="far fa-check-circle"></span>
				</button>
			{/if}
			<div class="btn-group btn-group-toggle" data-toggle="buttons">
				<label class="btn btn-sm btn-outline-primary {if $TYPE eq 'changes'}active{/if}">
					<input class="js-switch--recentActivities" type="radio" name="options" id="activities-option1" data-js="change"
						data-on-text="{App\Language::translate('LBL_CURRENT')}"
						data-on-val="changes"
						data-basic-text="{App\Language::translate('LBL_CURRENT')}"
						data-urlparams="whereCondition"
						autocomplete="off"> {\App\Language::translate('LBL_UPDATES', $MODULE_BASE_NAME)}
				</label>
				<label class="btn btn-sm btn-outline-primary {if $TYPE neq 'changes'}active{/if}">
					<input class="js-switch--recentActivities" type="radio" name="options" id="activities-option2" data-js="change"
						data-basic-text="{App\Language::translate('LBL_HISTORY')}"
						data-off-text="data-off-text {App\Language::translate('LBL_HISTORY')}"
						data-off-val="review"
						data-urlparams="whereCondition"
						autocomplete="off"> {\App\Language::translate('LBL_REVIEW_HISTORY', $MODULE_BASE_NAME)}
				</label>
			</div>
		</div>
	</div>
{/strip}
