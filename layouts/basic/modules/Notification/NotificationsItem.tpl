{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="media noticeRow" data-id="{$ROW->getId()}" data-type="{\App\Purifier::encodeHtml($ROW->get('type'))}">
		{assign var=ICON value=$ROW->getIcon()}
		<div class="media-body wordBreakAll">
			<div class="card mb-3">
				<div class="card-header p-2">
					{if $ICON}
						<div class="float-left">
							{if $ICON['type'] == 'image'}
								<img width="22px" class="mr-1 top2px {$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}" src="{$ICON['src']}" />
							{else}
								<span class="mr-1 noticeIcon {$ICON['class']}" title="{$ICON['title']}" alt="{$ICON['title']}"></span>
							{/if}
						</div>
					{/if}
					<div class="float-right">
						<small>
							{\App\Fields\DateTime::formatToViewDate($ROW->get('createdtime'))}
						</small>
					</div>
					<strong>{$ROW->getTitle()}</strong>
				</div>
				<div class="card-body p-2">
					{assign var=COTENT value=$ROW->getMessage()}
					{if $COTENT}
						{$COTENT}
						<hr/>
					{/if}
					<div class="text-right ">
						<b>{\App\Language::translate('Created By')}:</b>&nbsp;{$ROW->getCreatorUser()}&nbsp;
						<button type="button" class="btn btn-success btn-sm" onclick="Vtiger_Index_Js.markNotifications({$ROW->getId()});" title="{\App\Language::translate('LBL_MARK_AS_READ', $MODULE_NAME)}">
							<span class="fas fa-check"></span>
						</button>
						{assign var=RELATED_RECORD value=$ROW->getRelatedRecord()}
						{if $RELATED_RECORD['id'] && \App\Record::isExists($RELATED_RECORD['id'])}
							<a class="btn btn-info btn-sm ml-1" title="{\App\Language::translate('LBL_GO_TO_PREVIEW')}" href="index.php?module={$RELATED_RECORD['module']}&view=Detail&record={$RELATED_RECORD['id']}">
								<i class="fas fa-th-list"></i>
							</a>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
