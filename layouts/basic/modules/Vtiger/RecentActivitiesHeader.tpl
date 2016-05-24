{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		<div class="col-md-12">
			<div class="pull-right">
				<input class="switchBtn switchBtnReload recentActivitiesSwitch" type="checkbox" {if $TYPE eq 'changes'}checked=""{/if} data-size="small" data-label-width="5" data-on-text="{vtranslate('LBL_UPDATES', $MODULE_BASE_NAME)}" data-off-text="{vtranslate('LBL_REVIEW_HISTORY', $MODULE_BASE_NAME)}" data-urlparams="whereCondition" data-on-val="changes" data-off-val="review">
			</div>
		</div>
	</div>
{/strip}
