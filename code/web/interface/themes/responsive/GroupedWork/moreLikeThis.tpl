{strip}
	{if $recordDriver}
	<div id="moreLikeThisInfo" style="" class="row">
		<div class="col-sm-12">
			<div class="jcarousel-wrapper moreLikeThisWrapper">
				<div class="jcarousel horizontalCarouselSpotlight" id="moreLikeThisCarousel">
					<div class="loading">{translate text="Loading more titles like this title..."}</div>
				</div>

				<a href="#" class="jcarousel-control-prev" aria-label="{translate text="Previous Item" inAttribute=true}"><i class="fas fa-caret-left"></i></a>
				<a href="#" class="jcarousel-control-next" aria-label="{translate text="Next Item" inAttribute=true}"><i class="fas fa-caret-right"></i></a>
			</div>
		</div>
	</div>
	{/if}
{/strip}