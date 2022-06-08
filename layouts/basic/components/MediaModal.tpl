{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Components-MediaModal-->
	<div class="modal-body">
		<div class="row">
			<div class="col-md-6">
				<div class="input-group">
					<span class="input-group-prepend">
						<label class="input-group-text"><span class="fas fa-search"></span></label>
					</span>
					<input type="text" name="search" class="form-control js-icon-search" placeholder="{\App\Language::translate('LBL_SEARCH')}" autocomplete="off">
				</div>
			</div>
			<div class="col-md-6">
				<nav class="float-right">
					<input type="hidden" class="js-page-size" value="{$PAGE_LIMT}" />
					<ul class="pagination js-pagination-list mb-0">
						<li class="js-page--first page-item"
							data-id="1"
							data-js="data">
							<a class="page-link" href="#"><span
									class="fas fa-fast-backward mr-1 d-inline-block d-sm-none"></span><span
									class="d-none d-sm-inline">{\App\Language::translate('LBL_FIRST')}</span></a>
						</li>
						<li class="page-item js-page--previous">
							<a class="page-link" href="#" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span>
								<span class="sr-only">Previous</span>
							</a>
						</li>
						<li class="page-item js-page--set active" data-id="1"><a class="page-link" href="#">1</a></li>
						<li class="page-item js-page--next">
							<a class="page-link" href="#" aria-label="Next">
								<span aria-hidden="true">&raquo;</span>
								<span class="sr-only">Next</span>
							</a>
						</li>
						<li class="js-page--last page-item" data-js="click">
							<a class="page-link" href="#"><span
									class="fas fa-fast-forward mr-1 d-inline-block d-sm-none"></span><span
									class="d-none d-sm-inline">{\App\Language::translate('LBL_LAST')}</span></a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
		<div>
			<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
				<li class="nav-item col-6 p-0">
					<a class="nav-link active" href="#icons" data-toggle="tab" data-name="icons">
						<span class="fab fa-fonticons mr-2"></span>{\App\Language::translate('LBL_ICONS')}
					</a>
				</li>
				<li class="nav-item col-6 p-0">
					<a class="nav-link" href="#images" data-toggle="tab" data-name="images">
						<span class="far fa-images mr-2"></span>{\App\Language::translate('LBL_IMAGES')}
					</a>
				</li>
			</ul>
		</div>
		<div id="my-tab-content" class="tab-content">
			<div class="js-tab tab-pane active" id="icons" data-name="icons" data-js="data">
				<div id="icons-results" class="c-grid">
					{foreach from=$ICONS item=item name=icons}
						<article id="icon-{$smarty.foreach.icons.iteration}" class="w-100 {if $smarty.foreach.icons.iteration gt $PAGE_LIMT} d-none{/if} js-icon-item" data-icon-search="{strtolower($item.name)}" data-name="{$item.name}" data-type="{$item.type}">
							<button type="button" class="btn btn-light w-100 h-100">
								<span class="{$item.name} u-fs-xlg"> </span>
								<span class="c-grid-item--signature u-fs-xs">{$item.name}</span>
							</button>
						</article>
					{/foreach}
				</div>
			</div>
			<div class="js-tab tab-pane" id="images" data-name="images" data-js="data">
				{if !empty($FIELD_MODEL)}
					{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
					<input name="{$FIELD_MODEL->getFieldName()}_temp" type="file" class="d-none js-icon-file" data-js="jQuery-file-upload"
						data-url="{$FIELD_MODEL->getUITypeModel()->getUploadUrl()}" accept="{$FIELD_MODEL->getUITypeModel()->getAcceptFormats()}" data-fieldinfo="{$FIELD_INFO}">
					<button type="button" class="btn-xs btn btn-primary mb-2 js-image-add" aria-label="{App\Language::translate('LBL_ADD')}" data-fieldinfo="{$FIELD_INFO}">
						<span class="fas fa-plus mr-2"></span>{App\Language::translate('LBL_ADD')}
					</button>
				{/if}
				<div id="icons-results" class="c-grid">
					{foreach from=$IMAGES item=item name=images}
						<article id="image-{$smarty.foreach.images.iteration}" class="w-100 {if $smarty.foreach.images.iteration gt $PAGE_LIMT} d-none{/if} position-relative js-icon-item" data-icon-search="{\App\Purifier::encodeHtml(strtolower($item.name))}" data-name="{\App\Purifier::encodeHtml($item.name)}" data-type="image" data-key="{$item.key}">
							{if !empty($FIELD_MODEL)}
								<button type="button" class="btn-xs btn btn-danger js-popover-tooltip position-absolute u-position-r-0 js-image-remove" data-url="{$FIELD_MODEL->getUITypeModel()->getRemoveURL($item.key)}" data-js="popover" data-content="{App\Language::translate('LBL_REMOVE')}" aria-label="{App\Language::translate('LBL_REMOVE')}">
									<span aria-hidden="true">&times;</span>
								</button>
							{/if}
							<button type="button" class="btn btn-light w-100 h-100">
								<img class="icon-img--list" src="{$item.src}">
								<span class="c-grid-item--signature u-fs-xs">{\App\Purifier::encodeHtml($item.name)}</span>
							</button>
						</article>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Components-MediaModal-->
{/strip}
