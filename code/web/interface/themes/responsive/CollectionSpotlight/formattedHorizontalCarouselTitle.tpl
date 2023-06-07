{strip}
	<div id="scrollerTitle{$listName}{$key}" class="carouselScrollerTitle">
		<a href="{$titleURL}" tabindex="1">
			<div class="carouselScrollerTitleImage">
				<img src="{$imageUrl}" class="scrollerTitleCover" alt="{translate text="%1% Cover" 1=$title isPublicFacing=true inAttribute=true}" aria-hidden="true"/>
			</div>
			{if !empty($collectionSpotlight)}
				<div class="carouselScrollerTitleLabel">
					{if $collectionSpotlight->showTitle}
						<span>{$title}</span>
					{/if}
					{if $collectionSpotlight->showAuthor}
						&nbsp;<span>{translate text="by %1%" 1=$author isPublicFacing=true}</span>
					{/if}
				</div>
			{/if}
		</a>
		{* show ratings check in the template *}
		{include file="GroupedWork/title-rating.tpl" showNotInterested=false}
	</div>
{/strip}