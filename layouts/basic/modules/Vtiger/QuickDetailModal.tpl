{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-body col-md-12">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<div class="quickDetailContent">
			<div class="row">
				<div class="col-md-12">
					<div class="moduleIcon">
						<span class="detailViewIcon userIcon-{$MODULE_NAME}"></span>
					</div>
					<div class="paddingLeft5px">
						<h4 class="recordLabel u-text-ellipsis pushDown marginbottomZero" title='{$RECORD->getName()}'>
							<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
						</h4>
						{if $MODULE_NAME}
							<div class="paddingLeft5px">
								<span class="muted">
									{\App\Language::translate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
									{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
									{if $SHOWNERS != ''}
										<br />{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
									{/if}
								</span>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					{foreach key=key item=WIDGET from=$WIDGETS}
						<div class="quickDetailWidget">
							{if $WIDGET['title']}
								<h4>{$WIDGET['title']}</h4>
							{/if}
							<div>{$WIDGET['content']}</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}
