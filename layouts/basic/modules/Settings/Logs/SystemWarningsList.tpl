{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<table class="table table-bordered table-sm">
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
				<tr data-id="{get_class($ITEM)}" data-status="{$ITEM->getStatusValue()}">
					<td>{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}</td>
					<td class="text-center {if $ITEM->getStatus() == 0}bg-danger{elseif $ITEM->getStatus() == 1}bg-success{/if}">
						{if $ITEM->getStatus() == 0}
							<span class="fas fa-times"></span>
						{elseif $ITEM->getStatus() == 1}
							<span class="fas fa-check"></span>
						{elseif $ITEM->getStatus() == 2}
							<span class="fas fa-minus"></span>
						{/if}&nbsp;
						<span class="d-none">{$ITEM->getStatus()}</span>
					</td>
					<td data-order="{$ITEM->getPriority()}" class="text-center">{$ITEM->getPriority()}</td>
					<td class="text-center">
						{foreach from=$ITEM->getFolder() item=FOLDER name=FOLDERS}
							{App\Language::translate($FOLDER,'Settings:SystemWarnings')}{if not $smarty.foreach.FOLDERS.last}/{/if}
						{/foreach}
					</td>
					<td class="text-center">
						<button class="btn btn-warning btn-sm setIgnore js-popover-tooltip" data-js="popover" data-placement="top" data-content="
									{if $ITEM->getStatusValue() == 2}
										{App\Language::translate('BTN_REMOVE_IGNORE','Settings:SystemWarnings')}
									{else}
										{App\Language::translate('BTN_SET_IGNORE','Settings:SystemWarnings')}
									{/if}
									">
							{if $ITEM->getStatusValue() == 2}
								<span class="fas fa-plus-circle text-green"></span>
							{else}
								<span class="fas fa-minus-circle text-red"></span>
							{/if}
						</button>
						{if $ITEM->getLink()}
							<a class="ml-1 btn btn-success btn-sm {if isset($ITEM->linkTitle)}js-popover-tooltip{/if}" data-js="popover" href="{$ITEM->getLink()}" {if isset($ITEM->linkTitle)}data-placement="top" data-content="{$ITEM->linkTitle}" {/if} target="_blank" rel="noreferrer noopener">
								<span class="fas fa-link"></span>
							</a>
						{/if}
						{if $ITEM->getDescription()}
							<button class="ml-1 btn btn-primary btn-sm js-show-description" data-js="click">
								<span class="fas fa-info-circle"></span>
							</button>
							<span class="d-none js-show-description-content" data-js="container">
								<div class="modal fade">
									<div class="modal-dialog modal-xl">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title"><span class="fas fa-info-circle mr-2"></span>{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}</h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
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
