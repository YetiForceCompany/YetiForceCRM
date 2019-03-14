{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="tpl-OSSMail-GetMails modelContainer modal fade" id="sendEmailContainer" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-envelope mr-1"></span>
						{App\Language::translate('LBL_SELECT_EMAIL_IDS')}
					</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="container-fluid">
						<div>
							<h4>{App\Language::translate('LBL_MUTIPLE_EMAIL_SELECT_ONE')}</h4>
						</div>
						<div class="modal-Fields">
							{foreach from=$EMAILS item=ITEM}
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<div class="radio">
										<label class="btn btn-primary">
											<input class="mr-2" type="radio" name="selectedFields" value="{$ITEM['email']}">
											{$ITEM['fieldlabel']}: <strong>{$ITEM['email']}</strong>
										</label>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success selectButton">
						{App\Language::translate('LBL_SELECT')}
					</button>
					<button type="button" class="btn btn-warning" data-dismiss="modal">
						{App\Language::translate('LBL_CANCEL')}
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
