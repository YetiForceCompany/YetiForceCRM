{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=ENTRIES value=$NOTIFICATION_MODEL->getEntries()}
	<ul class="row notificationContainer gridster">
		{foreach from=$NOTIFICATION_MODEL->getTypes() item=TYPE key=TYPE_ID}
			{if $ENTRIES[$TYPE_ID]}
				<li data-row="1" data-col="1" data-sizex="{$TYPE['width']}" data-sizey="{$TYPE['height']}">
					<div class="panel panel-default">
						<div class="panel-heading">
							<div class="pull-right">
								<button type="button" class="btn btn-success btn-xs" onclick="Vtiger_Index_Js.markAllNotifications(this);" title="{vtranslate('LBL_MARK_AS_READ', $MODULE_NAME)}">
									<span class="glyphicon glyphicon-ok"></span>
								</button>
							</div>
							{vtranslate($TYPE['name'], $MODULE)}
						</div>
						<div class="panel-body notificationBody">
							<div class="notificationEntries">
								{foreach from=$ENTRIES[$TYPE_ID] item=ROW}
									{include file='NotificationsItem.tpl'|@vtemplate_path:$MODULE}
								{/foreach}
							</div>
						</div>
					</div>
				</li>
			{/if}
		{/foreach}
	</ul>
{/strip}
