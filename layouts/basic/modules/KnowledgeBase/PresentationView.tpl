{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="popupValue" value="{$IS_POPUP}"/>
	<div class="{if $IS_POPUP === false}col-12 {/if}knowledgePresentation">
		<div id="carouselPresentation" class="carousel slide" data-ride="carousel" data-interval="false">
			<div class="row{if $IS_POPUP === true} knowledgePresentationRow{/if}">
				<div class="knowledgePresentationInner">
					<div class="carousel-inner">
						{foreach from=$CONTENT item=SLIDE name=carousel}
							<div class="carousel-item {if $smarty.foreach.carousel.first} active{/if}">
								<div class="carousel-content knowledgePresentationContent">
									<div class="col-12 text-center">
										<h5>{$smarty.foreach.carousel.iteration}/{$smarty.foreach.carousel.total}</h5>
									</div>
									<div class="knowledgePresentationText col-12">
										{\App\Purifier::purifyHtml($SLIDE)}
									</div>
								</div>
							</div>
						{/foreach}
					</div>
				</div>
			</div>
			<a class="left carousel-control-prev text-secondary knowledgePresentationControl"
			   href="#carouselPresentation" role="button" data-slide="prev">
				<span class="fas fa-chevron-left fa-3x"
					  title="{\App\Language::translate('LBL_PREVIOUS',$MODULE_NAME)}"></span>
			</a>
			<a class="right carousel-control-next text-secondary knowledgePresentationControl"
			   href="#carouselPresentation" role="button" data-slide="next">
				<span class="fas fa-chevron-right fa-3x"
					  title="{\App\Language::translate('LBL_NEXT',$MODULE_NAME)}"></span>
			</a>
		</div>
	</div>
{/strip}
