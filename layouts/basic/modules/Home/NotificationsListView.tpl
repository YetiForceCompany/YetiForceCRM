{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<table class="table table-bordered table-condensed notificationTable">
		<thead>
			<tr>
				<th>{vtranslate('LBL_TITLE', $MODULE)}</th>
				<th>{vtranslate('LBL_TYPE_NOTIFICATIONS', $MODULE)}</th>
				<th>{vtranslate('LBL_MESSAGE', $MODULE)}</th>
				<th>{vtranslate('LBL_TIME', $MODULE)}</th>
				<th>
					{if !empty($NOTIFICATION_ENTRIES)}
						<button type="button" class="btn btn-success btn-xs" 
								onclick="Vtiger_Index_Js.markAllNotifications(this);" title="{vtranslate('LBL_MARK_AS_READ', $MODULE)}">
							<span class="glyphicon glyphicon-ok"></span>
						</button>
					{/if}
				</th>
			</tr>
		</thead>
		<tbody class="notificationEntries">
			{foreach from=$NOTIFICATION_ENTRIES item=ITEM}
				<tr class="noticeRow" data-id="{$ITEM->getId()}">
					<td>
						{assign var=ICON value=$ITEM->getIcon()}
						{if $ICON['type'] == 'image'}
							<img width="22px" class="top2px {$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" src="{$ICON['src']}"/>
						{else}
							<span class="noticeIcon {$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" aria-hidden="true"></span>
						{/if}&nbsp;&nbsp;
						{$ITEM->getTitle()}
					</td>
					<td>{vtranslate($ITEM->getTypeName(), $MODULE)}</td>
					<td>{$ITEM->getMassage()}</td>
					<td>
						<span title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($ITEM->get('time'))}">
							{Vtiger_Util_Helper::formatDateDiffInStrings($ITEM->get('time'))}
						</span>
					</td>
					<td class="text-nowrap">
						<button type="button" class="btn btn-success btn-xs" onclick="Vtiger_Index_Js.markNotifications({$ITEM->getId()});" title="{vtranslate('LBL_MARK_AS_READ', $MODULE)}">
							<span class="glyphicon glyphicon-ok"></span>
						</button>&nbsp;&nbsp;
						{if isRecordExists($ITEM->get('reletedid'))}
							<a class="btn btn-info btn-xs" title="{vtranslate('LBL_GO_TO_PREVIEW')}" href="index.php?module={$ITEM->get('reletedmodule')}&view=Detail&record={$ITEM->get('reletedid')}">
								<span class="glyphicon glyphicon-th-list"></span>
							</a>
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}
