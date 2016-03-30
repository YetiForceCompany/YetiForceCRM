{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class="recentActivitiesContainer" >
	<input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}" />
	<input type="hidden" id="updatesPageLimit" value="{$PAGING_MODEL->getPageLimit()}" />
		{if !empty($RECENT_ACTIVITIES)}
			<ul class="timeline" id="updates">
				{foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
					{assign var=PROCEED value= TRUE}
					{if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
						{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
						{if !($RELATION->getLinkedRecord())}
							{assign var=PROCEED value= FALSE}
						{/if}
					{/if}
					{if $PROCEED}
						{if $RECENT_ACTIVITY->isCreate()}
							<li>
								<span class="glyphicon glyphicon-plus bgGreen"></span>
								<div class="timeline-item">
									<span class="time">
										<b>{$RECENT_ACTIVITY->getActivityTime()}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getParent()->get('createdtime'))})
									</span>
									<div class="timeline-body row no-margin">
										<div class="pull-left paddingRight15">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="pull-left">
											<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong> 
											{vtranslate('LBL_CREATED', $MODULE_NAME)}
										</div>
									</div>
								</div>
							</li>
						{else if $RECENT_ACTIVITY->isUpdate()}
							<li>
								<span class="glyphicon glyphicon-pencil bgDarkBlue"></span>
								<div class="timeline-item">
									<span class="time">
										<b>{$RECENT_ACTIVITY->getActivityTime()}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())})
									</span>
									<div class="timeline-body row no-margin">
										<div class="pull-left paddingRight15">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="pull-left">
											<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}</strong> {vtranslate('LBL_UPDATED', $MODULE_NAME)}</span>
											{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
												{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
													<div class='font-x-small updateInfoContainer'>
														<span>{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</span>:&nbsp;
															{if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
																&nbsp;{vtranslate('LBL_FROM')} <strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue')))),$MODULE_NAME)}</strong>
															{else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																&nbsp; <strong> {vtranslate('LBL_DELETED')} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</del> )
															{else}
																&nbsp;{vtranslate('LBL_CHANGED')}
															{/if}
															{if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
																&nbsp;{vtranslate('LBL_TO')}&nbsp;<strong style="white-space:pre-wrap;">
																{vtranslate(Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue')))),$MODULE_NAME)}</strong>
															{/if}
													</div>
												{/if}
											{/foreach}
										</div>
									</div>
								</div>
							</li>
						{else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
							<li>
								<span class="glyphicon glyphicon-link bgOrange"></span>
								<div class="timeline-item">
									<span class="time">
										<b>{$RECENT_ACTIVITY->getActivityTime()}</b> ({Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATION->get('changedon'))})
									</span>
									<div class="timeline-body row no-margin">
										<div class="pull-left paddingRight15">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="pull-left">
										{assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
											<span>{vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}</span> <span>
												{if $RECENT_ACTIVITY->isRelationLink()}
													{vtranslate('LBL_ADDED', $MODULE_NAME)}
												{else}
													{vtranslate('LBL_REMOVED', $MODULE_NAME)}
												{/if} </span><span>
												{if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
													{if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {else} {/if}
												{else} <strong>{$RELATION->getLinkedRecord()->getName()}</strong> {/if}</span>
										</div>
									</div>
								</div>
							</li>
						{else if $RECENT_ACTIVITY->isRestore()}
							<li>
								
							</li>
						{else if $RECENT_ACTIVITY->isConvertToAccount()}
							<li>
								<span class="glyphicon glyphicon-transfer bgAzure"></span>
								<div class="timeline-item">
									<span class="time">
										<b>{$RECENT_ACTIVITY->getActivityTime()}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())})
									</span>
									<div class="timeline-body row no-margin">
										<div class="pull-left paddingRight15">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="pull-left">
											<strong>{vtranslate('LBL_CONVERTED_FROM_LEAD', $MODULE_NAME)}</strong> 
										</div>
									</div>
								</div>
							</li>
						{else if $RECENT_ACTIVITY->isDisplayed()}
							<li>
								<span class="glyphicon glyphicon-th-list bgBlue"></span>
								<div class="timeline-item">
									<span class="time">
										<b>{$RECENT_ACTIVITY->getActivityTime()}</b> ({Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())})
									</span>
									<div class="timeline-body row no-margin">
										<div class="pull-left paddingRight15">
											<img class="userImage img-circle" src="{$RECENT_ACTIVITY->getModifiedBy()->getImagePath()}">
										</div>
										<div class="pull-left">
											<strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong>
											{vtranslate('LBL_DISPLAYED', $MODULE_NAME)}
										</div>
									</div>
								</div>
							</li>
						{/if}
					{/if}
				{/foreach}
			</ul>
			{else}
				<div class="summaryWidgetContainer">
					<p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
				</div>
		{/if}
	
		<div id="moreLink">
		    {if $PAGING_MODEL->isNextPageExists()}
			<div class="pull-right">
				<a href="javascript:void(0)" class="moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
			</div>
		    {/if}
		</div>

</div>
{/strip}
