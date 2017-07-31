{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{assign var="AMODULE" value='Announcements'}
	<div id="announcements">
		{foreach item=ANNOUNCEMENT from=$ANNOUNCEMENTS->getAnnouncements()}
			<div class="announcement hide" data-id="{$ANNOUNCEMENT->getId()}">
				<div class="modal fade">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">{$ANNOUNCEMENT->get('subject')}</h4>
							</div>
							<div class="modal-body">
								{$ANNOUNCEMENT->get('description')}
							</div>
							<div class="modal-footer">
								{if !$ANNOUNCEMENT->get('is_mandatory')}
									<button type="button" class="btn btn-danger" data-type="0">
										<span class="glyphicon glyphicon-time" aria-hidden="true"></span>&nbsp;
										{\App\Language::translate('LBL_REMIND_ME_LATER',$AMODULE)}
									</button>
								{/if}
								<button type="button" class="btn btn-success" data-type="1">
									<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;
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
