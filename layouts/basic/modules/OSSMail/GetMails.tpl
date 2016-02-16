{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div id="sendEmailContainer" class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 class="modal-title">{vtranslate('LBL_SELECT_EMAIL_IDS', 'Emails')}</h3>
				</div>
				<div class="modal-body">
					<div class="container-fluid">
						<div>
							<h4>{vtranslate('LBL_MUTIPLE_EMAIL_SELECT_ONE', 'Vtiger')}</h4>
						</div>
						<div class="modal-Fields">
							{foreach from=$EMAILS item=ITEM}
								<div class="form-group">
									<div class="radio">
										<label>
											<input style="float: right;" type="radio" name="selectedFields" value="{$ITEM['email']}">
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
						{vtranslate('LBL_SELECT', 'Vtiger')}
					</button>
					<button type="button" class="btn btn-warning" data-dismiss="modal">
						{vtranslate('LBL_CANCEL', 'Vtiger')}
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
