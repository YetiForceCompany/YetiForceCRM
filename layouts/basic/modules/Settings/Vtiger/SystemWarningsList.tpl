{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>{App\Language::translate('LBL_WARNINGS_TITLE', $MODULE)}</th>
				<th>{App\Language::translate('LBL_WARNINGS_STATUS', $MODULE)}</th>
				<th>{App\Language::translate('LBL_WARNINGS_PRIORITY', $MODULE)}</th>
				<th>{App\Language::translate('LBL_WARNINGS_FOLDER', $MODULE)}</th>
				<th></th>
			</tr>
		</thead>
		<tbody class="notificationEntries">
			{foreach from=$WARNINGS_LIST item=ITEM}
				<tr data-id="{get_class($ITEM)}" data-status="{$ITEM->getStatus()}">
					<td>{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}</td>
					<td class="text-center {if $ITEM->getStatus() == 0}danger{elseif $ITEM->getStatus() == 1}success{/if}">
						{if $ITEM->getStatus() == 0}
							<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
						{elseif $ITEM->getStatus() == 1}
							<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
						{elseif $ITEM->getStatus() == 2}
							<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
						{/if}&nbsp;
					</td>
					<td data-order="{$ITEM->getPriority()}" class="text-center">{$ITEM->getPriority()}</td>
					<td class="text-center">
						{foreach from=$ITEM->getFolder() item=FOLDER name=FOLDERS}
							{App\Language::translate($FOLDER,'Settings:SystemWarnings')}{if not $smarty.foreach.FOLDERS.last}/{/if} 
						{/foreach}
					</td>
					<td class="text-center">
						{if $ITEM->getStatus() != 1 && $ITEM->getPriority() < 8}
							<button class="btn btn-warning btn-xs setIgnore popoverTooltip" data-placement="top" data-content="
									{if $ITEM->getStatus() == 2}
										{App\Language::translate('BTN_REMOVE_IGNORE','Settings:SystemWarnings')}
									{else}
										{App\Language::translate('BTN_SET_IGNORE','Settings:SystemWarnings')}
									{/if}
									">
								{if $ITEM->getStatus() == 2}
									<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
								{else}
									<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
								{/if}
							</button>&nbsp;&nbsp;
						{/if}
						{if $ITEM->getLink()}
							<a class="btn btn-success btn-xs {if isset($ITEM->linkTitle)}popoverTooltip{/if}" href="{$ITEM->getLink()}" {if isset($ITEM->linkTitle)}data-placement="top" data-content="{$ITEM->linkTitle}"{/if} target="_blank">
								<span class="glyphicon glyphicon-link" aria-hidden="true"></span>
							</a>&nbsp;&nbsp;
						{/if}
						{if $ITEM->getDescription()}
							<button class="btn btn-primary btn-xs showDescription">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
							</button>
							<span class="hide showDescriptionContent">
								<div class="modal fade">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												<h4 class="modal-title" id="myModalLabel">{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}</h4>
											</div>
											<div class="modal-body">
												{$ITEM->getDescription()}
											</div>
										</div>
									</div>
								</div>
							</span>
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}
