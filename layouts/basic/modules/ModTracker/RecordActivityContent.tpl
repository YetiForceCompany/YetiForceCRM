{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ModTracker-RecordActivityContent -->
	{if $UPDATES}
		{foreach item=UPDATE_ROW from=$UPDATES}
			{assign var=MODIFIER_IMAGE value=$UPDATE_ROW->getModifiedBy()->getImage()}
			{assign var=MODIFIER_NAME value=\App\Purifier::encodeHtml($UPDATE_ROW->getModifierName())}
			{assign var=TIME value=$UPDATE_ROW->getActivityTime()}
			{assign var=PARENT value=$UPDATE_ROW->getParent()}
			{assign var=PROCEED value=true}
			{if ($UPDATE_ROW->isRelationLink()) or ($UPDATE_ROW->isRelationUnLink())}
				{assign var=RELATION value=$UPDATE_ROW->getRelationInstance()}
				{if !($RELATION->getValue())}
					{assign var=PROCEED value=false}
				{/if}
			{/if}
			{if $PROCEED}
				<div class="d-flex u-fs-14px">
					<div class="w-100">
						<div class="mr-1 float-sm-left imageContainer text-center q-avatar u-fs-38px">
							{if $MODIFIER_IMAGE}
								<img class="userImage align-text-top" src="{$MODIFIER_IMAGE['url']}">
							{else}
								<span class="fas fa-user userImage align-text-top u-fs-2x"></span>
							{/if}
						</div>
						{assign var=DETAILVIEW_URL value=$PARENT->getDetailViewUrl()}
						{if $UPDATE_ROW->isUpdate() || $UPDATE_ROW->isTransferEdit()}
							{assign var=FIELDS value=$UPDATE_ROW->getFieldInstances()}
							<div>
								<div class="d-flex">
									<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
										<strong>
											{$MODIFIER_NAME}&nbsp;
										</strong>
									</div>
								</div>
								<div class="u-white-space-nowrap u-text-ellipsis--no-hover ">
									<span class="mr-1" style="color: {ModTracker::$colorsActions[$UPDATE_ROW->get('status')]};">
										<span class="{ModTracker::$iconActions[$UPDATE_ROW->get('status')]} fa-fw"></span>
									</span>
									{\App\Utils::mbUcfirst(\App\Language::translate($UPDATE_ROW->getStatusLabel(), $SOURCE_MODULE_NAME))}
									<span class="text-muted float-right">
										<small>{\App\Fields\DateTime::formatToViewDate("$TIME")}</small>
									</span>
									{assign var=COUNTER value=0}
									{foreach from=$FIELDS item=FIELD}
										{if $FIELD && $FIELD->getFieldInstance() && $FIELD->getFieldInstance()->isViewableInDetailView()}
											{assign var=COUNTER value=$COUNTER+1}
											{assign var=DISPLAY_TEXT value=''}
											{assign var=DISPLAY_TEXT_POST value=''}
											{assign var=DISPLAY_TEXT_PRE value="<span>{\App\Language::translate($FIELD->getName(), $FIELD->getModuleName())}: </span>"}
											{if $FIELD->get('prevalue') neq '' && $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELD->get('postvalue') eq '0' || $FIELD->get('prevalue') eq '0'))}
												{assign var=DISPLAY_TEXT value="&nbsp;{\App\Language::translate('LBL_FROM')}&nbsp; <strong>{Vtiger_Util_Helper::toVtiger6SafeHTML(App\Purifier::decodeHtml($FIELD->getOldValue()))}</strong>"}
											{else if $FIELD->get('postvalue') eq '' || ($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
												{assign var=DISPLAY_TEXT value="&nbsp; <strong> {\App\Language::translate('LBL_DELETED', $SOURCE_MODULE_NAME)} </strong> ( <del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getOldValue())}</del> )"}
											{else}
												{assign var=DISPLAY_TEXT value="&nbsp;{\App\Language::translate('LBL_CHANGED')}"}
											{/if}
											{if $FIELD->get('postvalue') neq '' && !($FIELD->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELD->get('postvalue') eq '0')}
												{assign var=DISPLAY_TEXT_POST value="&nbsp;{\App\Language::translate('LBL_TO')}&nbsp;
				<strong>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELD->getNewValue())}</strong>"}
											{/if}
											{assign var=DISPLAY_TEXT_FULL value="{$DISPLAY_TEXT_PRE}{$DISPLAY_TEXT}{$DISPLAY_TEXT_POST}"}
											<div class='font-x-small js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center'
												data-content="{\App\Purifier::encodeHtml($DISPLAY_TEXT_FULL)}" data-toggle="popover" data-js="popover | mouseenter">
												<span class="js-popover-text" data-js="clone">
													<span>{$DISPLAY_TEXT_FULL}</span>
												</span>
												<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
											</div>
											{if $COUNTER eq 3}
												<a class="btn moreBtn badge badge-info" href="{$PARENT->getUpdatesUrl()}">{\App\Language::translate('LBL_MORE')}</a>
												{break}
											{/if}
										{/if}
									{/foreach}
								</div>
							</div>
						{else if ($UPDATE_ROW->isRelationLink() || $UPDATE_ROW->isRelationUnLink() || $UPDATE_ROW->isTransferLink() || $UPDATE_ROW->isTransferUnLink())}
							{assign var=RELATION value=$UPDATE_ROW->getRelationInstance()}
							<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
								<div class="d-flex">
									<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
										<strong>
											{$MODIFIER_NAME}&nbsp;
										</strong>
									</div>
								</div>
								<div>
									<span class="mr-1" style="color: {ModTracker::$colorsActions[$UPDATE_ROW->get('status')]};">
										<span class="{ModTracker::$iconActions[$UPDATE_ROW->get('status')]} fa-fw"></span>
									</span>
									{\App\Utils::mbUcfirst(\App\Language::translate($UPDATE_ROW->getStatusLabel(), $SOURCE_MODULE_NAME))}&nbsp;
									<span class="text-muted float-right">
										<small>{\App\Fields\DateTime::formatToViewDate("$TIME")}</small>
									</span>
									<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
										{assign var=DISPLAY_TEXT value=$RELATION->getValue()}
										{if $DISPLAY_TEXT}
											{if $RELATION->get('targetmodule') eq 'ModComments'}
												{assign var=IS_PERMITTED_RECORD value=false}
												{assign var=DISPLAY_TEXT value=\App\Utils\Completions::decode(Vtiger_Util_Helper::toVtiger6SafeHTML(\App\Purifier::decodeHtml($RELATION->getValue())))}
											{else}
												{assign var=IS_PERMITTED_RECORD value=\App\Privilege::isPermitted($RELATION->get('targetmodule'), 'DetailView', $RELATION->get('targetid'))}
											{/if}
											<span class="yfm-{$RELATION->get('targetmodule')} fa-lg fa-fw mr-1"
												title="{\App\Language::translateSingularModuleName($RELATION->get('targetmodule'))}"></span>
											<span {if $IS_PERMITTED_RECORD}
													class="js-popover-tooltip--ellipsis u-text-ellipsis--no-hover" data-toggle="popover"
													data-content="{\App\Purifier::encodeHtml($DISPLAY_TEXT)}"
												data-js="popover" {else}class="text-truncate"
												{/if}>
												{if $IS_PERMITTED_RECORD}
													<a class="modCT_{$RELATION->get('targetmodule')} js-popover-tooltip--record"
														href="{$RELATION->getDetailViewUrl()}">
														{$DISPLAY_TEXT}
													</a>
												{else}
													<strong>{$DISPLAY_TEXT}</strong>
												{/if}
											</span>
										{/if}
									</div>
								</div>
							</div>
						{else}
							<div class="">
								<div class="d-flex">
									<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
										<strong>
											{$MODIFIER_NAME}&nbsp;
										</strong>
									</div>
								</div>
								<div>
									<span class="mr-1" style="color: {ModTracker::$colorsActions[$UPDATE_ROW->get('status')]};">
										<span class="{ModTracker::$iconActions[$UPDATE_ROW->get('status')]} fa-fw"></span>
									</span>
									{\App\Utils::mbUcfirst(\App\Language::translate($UPDATE_ROW->getStatusLabel(), $SOURCE_MODULE_NAME))}
									<span class="text-muted float-right">
										<small>{\App\Fields\DateTime::formatToViewDate("$TIME")}</small>
									</span>
								</div>
							</div>
						{/if}
					</div>
				</div>
			{/if}
		{/foreach}
	{/if}
	<!-- /tpl-ModTracker-RecordActivityContent -->
{/strip}
