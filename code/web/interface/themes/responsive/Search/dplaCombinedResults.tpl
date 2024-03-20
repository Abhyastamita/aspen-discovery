{strip}
	<div id="dplaSearchResults">
		{foreach from=$searchResults item=result name="recordLoop"}
			<div class="result">
				<div class="dplaResult resultsList row">
					{if !empty($showCovers)}
						<div class="coversColumn col-xs-3 text-center">
							{if $disableCoverArt != 1}
								{if !empty($result.object)}
									<a href="{$result.link}">
										<img src="{$result.object}" class="listResultImage img-thumbnail {$coverStyle}" alt="{translate text='Cover Image' inAttribute=true isPublicFacing=true}">
									</a>
								{/if}
							{/if}
						</div>
					{/if}
					<div class="{if !empty($showCovers)}col-xs-9{else}col-xs-12{/if}">
						<div class="row">
							<div class="col-xs-12">
								<span class="result-index">{$smarty.foreach.recordLoop.iteration})</span>&nbsp;
								<a href="{$result.link}" class="result-title notranslate">
									{if !$result.title|removeTrailingPunctuation} {translate text='Title not available' isPublicFacing=true}{else}{$result.title|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
								</a>
							</div>
						</div>

						{if !empty($result.format)}
							<div class="row">
								<div class="result-label col-tn-3">{translate text='Format' isPublicFacing=true}:</div>
								<div class="col-tn-9 result-value">{translate text=$result.format|escape isPublicFacing=true}</div>
							</div>
						{/if}

						{if !empty($result.description)}
							<div class="row well-small">
								<div class="col-tn-12 result-value">{$result.description|truncate_html:450:"..."|strip_tags|escape}</div>
							</div>
						{/if}
					</div>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}