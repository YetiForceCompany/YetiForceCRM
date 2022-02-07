{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var="AMODULE" value='Announcements'}
	<div id="announcements">
		{foreach item=ANNOUNCEMENT from=$ANNOUNCEMENTS->getAnnouncements()}
			<div class="announcement d-none" data-id="{$ANNOUNCEMENT->getId()}">
				<div class="modal fade">
					<div class="modal-dialog modal-xl">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">{$ANNOUNCEMENT->getDisplayValue('subject')}</h5>
							</div>
							<div class="modal-body">
								{$ANNOUNCEMENT->getDisplayValue('description',false,false,'full')}
							</div>
							<div class="modal-footer">
								{if !$ANNOUNCEMENT->get('is_mandatory')}
									<button type="button" class="btn btn-danger" data-type="0">
										<span class="far fa-clock"></span>&nbsp;
										{\App\Language::translate('LBL_REMIND_ME_LATER',$AMODULE)}
									</button>
								{/if}
								<button type="button" class="btn btn-success" data-type="1">
									<span class="fas fa-check"></span>&nbsp;
									{\App\Language::translate('LBL_MARK_AS_READ',$AMODULE)}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}
