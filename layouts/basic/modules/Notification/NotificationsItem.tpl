{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="NotificationsItem media noticeRow" data-id="{$ROW->getId()}" data-type="{\App\Purifier::encodeHtml($ROW->get('type'))}">
		{assign var=ICON value=$ROW->getIcon()}
		<div class="media-body wordBreakAll">
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="card-header p-2">
					{if $ICON}
						<div class="float-left">
							{assign var=IMAGE_PATH value=$ICON['src']}
							{if $IMAGE_PATH}
								<img class="userImage float-left" src="data:image/jpg;base64,{base64_encode(file_get_contents($IMAGE_PATH))}" >
							{else}	
								<span class="mr-1 fas fa-user userImage"></span>
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
							<a class="btn btn-info btn-sm ml-1" role="button" href="index.php?module={$RELATED_RECORD['module']}&view=Detail&record={$RELATED_RECORD['id']}">
								<span class="fas fa-th-list" title="{\App\Language::translate('LBL_GO_TO_PREVIEW')}"></span>
							</a>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
