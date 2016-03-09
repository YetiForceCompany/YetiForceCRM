{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=ENTRIES value=$NOTIFICATION_MODEL->getEntries()}
	<ul class="row notificationContainer gridster">
		{foreach from=$NOTIFICATION_MODEL->getTypes() item=TYPE key=TYPE_ID}
			{if $ENTRIES[$TYPE_ID]}
				<li  data-row="1" data-col="1" data-sizex="{$TYPE['width']}" data-sizey="{$TYPE['height']}">
					<div class="panel panel-default">
						<div class="panel-heading">
							{vtranslate($TYPE['name'], $MODULE)}
						</div>
						<div class="panel-body">
							<div class="notificationEntries">
								{foreach from=$ENTRIES[$TYPE_ID] item=ENTRY}
									<div class="media noticeRow" data-id="{$ENTRY->getId()}" data-type="{$ENTRY->get('type')}">
										<div class="media-left media-middle">
											{assign var=ICON value=$ENTRY->getIcon()}
											{if $ICON}
												{if $ICON['type'] == 'image'}
													<img width="30px" class="{$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" src="{$ICON['src']}"/>
												{else}
													<span class="{$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" aria-hidden="true"></span>
												{/if}
											{/if}
										</div>
										<div class="media-body media-middle">
											{$ENTRY->getMassage()}
											<div class="pull-right">
												{Vtiger_Util_Helper::formatDateDiffInStrings($ENTRY->get('time'))}
											</div>
										</div>
										<div class="media-right media-middle">
											{foreach from=$ENTRY->getActions() item=ACTION}
												<button class="btn {$ACTION['class']}" {if $ACTION['action']}onclick="{$ACTION['action']}"{/if} type="button">
													{if $ACTION['name']}
														{vtranslate($ACTION['name'], $MODULE)}
													{/if}
													{if $ACTION['icon']}
														<span class="{$ACTION['icon']}" title="{{vtranslate($ACTION['title'], $MODULE)}}" aria-hidden="true"></span>
													{/if}
												</button>
											{/foreach}
										</div>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				</li>
			{/if}
		{/foreach}
	</ul>
{/strip}
