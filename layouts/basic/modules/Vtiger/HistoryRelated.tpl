{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="recentActivitiesContainer" >
		{if !empty($HISTORIES)}
			<ul class="timeline" id="updates">
				{foreach item=HISTORY from=$HISTORIES}
					<li>
						<span class="glyphicon glyphicon-th-list bgBlue"></span>
						<div class="timeline-item">
							<span class="time">
								<b>{$HISTORY['date_start']}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($HISTORY['date_start'])})
							</span>
							<div class="timeline-body row no-margin">
								<div class="pull-left paddingRight15">
									<img class="userImage img-circle" src="{$HISTORY['userModel']->getImagePath()}">
								</div>
								<div class="pull-left">
									<strong>{$HISTORY['userModel']->getName()}</strong>
									{$HISTORY['subject']} <br><br>
									{vtranslate($HISTORY['activitytype'],$MODULE_NAME)}
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
		{else}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
			</div>
		{/if}
		<div id="moreLink">
			<div class="pull-right">
				<a href="javascript:void(0)" class="moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
			</div>
		</div>
	</div>
{/strip}
