{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Modals-QuickDetailModal modal js-modal-data {if $LOCK_EXIT}static{/if}" tabindex="-1" data-js="data"
		 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
		<div class="modal-dialog {$MODAL_VIEW->modalSize}" role="document">
			<div class="modal-content">
				{foreach item=MODEL from=$MODAL_CSS}
					<link rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
				{/foreach}
				{foreach item=MODEL from=$MODAL_SCRIPTS}
					<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
				{/foreach}
				<script type="text/javascript">app.registerModalController();</script>
				<div class="modal-body col-md-12 js-scrollbar" data-js="perfectscrollbar">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<div class="quickDetailContent">
						<div class="row">
							<div class="col-md-12">
								<div class="moduleIcon">
									<span class="o-detail__icon js-detail__icon userIcon-{$MODULE_NAME}"></span>
								</div>
								<div class="paddingLeft5px">
									<h4 class="recordLabel u-text-ellipsis pushDown marginbottomZero" title='{$RECORD->getName()}'>
										<span class="modCT_{$MODULE_NAME}">{$RECORD->getName()}</span>
									</h4>
									{if $MODULE_NAME}
										<div class="paddingLeft5px">
								<span class="muted">
									{\App\Language::translate('Assigned To',$MODULE_NAME)}
									: {$RECORD->getDisplayValue('assigned_user_id')}
									{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
									{if $SHOWNERS != ''}
										<br/>
										{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
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
										{if !empty($WIDGET['title'])}
											<h4>{$WIDGET['title']}</h4>
										{/if}
										<div>{$WIDGET['content']}</div>
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
