{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="recentActivitiesContainer" >
		<input type="hidden" id="relatedHistoryCurrentPage" value="{$PAGING_MODEL->get('page')}" />
		<input type="hidden" id="relatedHistoryPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($HISTORIES)}
			<ul class="timeline" id="relatedUpdates">
				{foreach item=HISTORY from=$HISTORIES}
					<li>
						<span class="glyphicon {$HISTORY['class']} userIcon-{$HISTORY['type']}" aria-hidden="true"></span>
						<div class="timeline-item">
							<span class="time">
								<b>{$HISTORY['time']}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($HISTORY['time'])})
							</span>
							<div class="timeline-body row no-margin">
								<div class="pull-left paddingRight15">
									{if !$HISTORY['isGroup']}
										<img class="userImage img-circle" src="{$HISTORY['userModel']->getImagePath()}">
									{else}
										<img class="userImage img-circle" src="{vimage_path('DefaultUserIcon.png')}">
									{/if}
								</div>
								<div class="pull-left">
									<strong>{$HISTORY['userModel']->getName()}&nbsp;</strong>
									<a href="{$HISTORY['url']}" target="_blank">{$HISTORY['content']}</a>
								</div>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
			{if count($HISTORIES) eq $PAGING_MODEL->getPageLimit()}
				<div id="moreRelatedUpdates">
					<div class="pull-right">
						<a href="javascript:void(0)" class="moreRelatedUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
					</div>
				</div>
			{/if}
		{else}
			{if $PAGING_MODEL->get('page') eq 1}
				<div class="summaryWidgetContainer">
					<p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
				</div>
			{/if}
		{/if}

	</div>
{/strip}
