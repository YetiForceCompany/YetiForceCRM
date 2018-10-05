{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Chat o-action-menu__item">
		<a class="c-header__btn ml-2 btn btn-light btn headerLinkChat js-popover-tooltip"
		   role="button"
		   data-js="popover" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
		<span class="fas fa-comments fa-fw"
			  title="{\App\Language::translate('LBL_CHAT')}"></span>
			<span class="c-header__label--sm-down"> {\App\Language::translate('LBL_CHAT')}</span>
		</a>
		<div class="chatModal modal fade c-modal--custom-animation" tabindex="-1" role="dialog"
			 aria-labelledby="c-chat-modal__title"
			 data-timer="{AppConfig::module('Chat', 'REFRESH_TIME')}000">
			<div class="modal-dialog modalRightSiteBar px-0" role="document">
				<div class="modal-content rounded-0">
					<div class="modal-header">
						<h5 class="modal-title" id="c-chat-modal__title">
							<span class="fas fa-comments fa-fw mr-1"></span>
							{\App\Language::translate('LBL_CHAT')}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body js-chat-items" data-js="html">
						{include file=\App\Layout::getTemplatePath('Items.tpl', 'Chat')}
					</div>
					<div class="modal-footer pinToDown row mx-0 d-block">
						<label for="c-chat-modal__message">{\App\Language::translate('LBL_MESSAGE', 'Notification')}</label>
						<input class="form-control message" id="c-chat-modal__message" type="text"/>
						<button type="button" class="btn btn-primary addMsg float-right mt-2" data-js="click">
							{\App\Language::translate('LBL_SEND_MESSAGE')}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
