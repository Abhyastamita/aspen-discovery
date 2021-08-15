{strip}
	<div id="record{$record->source}_{$record->sourceId|escape}" class="result row{if $record->isOverdue()} bg-overdue{/if}">

		{* Cover Column *}
		{if $showCovers}
			{*<div class="col-xs-4">*}
			<div class="col-xs-3 col-sm-4 col-md-3">
				<div class="row">
					<div class="selectTitle col-xs-12 col-sm-1">
						{if !isset($record->canRenew) || $record->canRenew == true}
						<input type="checkbox" name="selected[{$record->userId}|{$record->recordId}|{$record->renewIndicator}]" class="titleSelect" id="selected{$record->itemId}">
						{/if}
					</div>
					<div class="{*coverColumn *}text-center col-xs-12 col-sm-10">
						{if $disableCoverArt != 1}{*TODO: should become part of $showCovers *}
							{if $record->getCoverUrl()}
								{if $record->recordId && !empty($record->getLinkUrl())}
									<a href="{$record->getLinkUrl()}" id="descriptionTrigger{$record->recordId|escape:"url"}" aria-hidden="true">
										<img src="{$record->getCoverUrl()}" class="listResultImage img-thumbnail img-responsive" alt="{translate text='Cover Image' inAttribute=true}">
									</a>
								{else} {* Cover Image but no Record-View link *}
									<img src="{$record->getCoverUrl()}" class="listResultImage img-thumbnail img-responsive" alt="{translate text='Cover Image' inAttribute=true}" aria-hidden="true">
								{/if}
							{/if}
						{/if}
					</div>
				</div>
			</div>
		{else}
			<div class="col-xs-1">
				{if !isset($record->canRenew) || $record->canRenew == true}
					<input type="checkbox" name="selected[{$record->userId}|{$record->recordId}|{$record->renewIndicator}]" class="titleSelect" id="selected{$record->itemId}">
				{/if}
			</div>
		{/if}

		{* Title Details Column *}
		<div class="{if $showCovers}col-xs-9 col-sm-8 col-md-9{else}col-xs-11{/if}">
			{* Title *}
			<div class="row">
				<div class="col-xs-12">
					<span class="result-index">{$resultIndex})</span>&nbsp;
					{if $record->getLinkUrl()}
						<a href="{$record->getLinkUrl()}" class="result-title notranslate">
							{if !$record->getTitle()|removeTrailingPunctuation}{translate text='Title not available'}{else}{$record->getTitle()|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
						</a>
					{else}
						<span class="result-title notranslate">
							{if !$record->getTitle()|removeTrailingPunctuation}{translate text='Title not available'}{else}{$record->getTitle()|removeTrailingPunctuation|truncate:180:"..."|highlight}{/if}
						</span>
					{/if}
					{if !empty($record->title2)}
						<div class="searchResultSectionInfo">
							{$record->title2|removeTrailingPunctuation|truncate:180:"..."|highlight}
						</div>
					{/if}
				</div>
			</div>

			<div class="row">
				<div class="resultDetails col-xs-12 col-md-9">
					{if !empty($record->volume)}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Volume'}</div>
							<div class="result-value col-tn-8 col-lg-9">{$record->volume|escape}</div>
						</div>
					{/if}

					{if $record->getAuthor()}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Author'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{if is_array($record->getAuthor())}
									{foreach from=$record->getAuthor() item=author}
										<a href='/Author/Home?author="{$author|escape:"url"}"'>{$author|highlight}</a>
									{/foreach}
								{else}
									<a href='/Author/Home?author="{$record->getAuthor()|escape:"url"}"'>{$record->getAuthor()|highlight}</a>
								{/if}
							</div>
						</div>
					{/if}

                    {if !empty($record->callNumber)}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Call Number'}</div>
							<div class="col-tn-8 col-lg-9 result-value">
                                {$record->callNumber}
							</div>
						</div>
                    {/if}

					{if $showOut}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Checked Out'}</div>
							<div class="result-value col-tn-8 col-lg-9">{$record->checkoutDate|date_format}</div>
						</div>
					{/if}

					<div class="row">
						<div class="result-label col-tn-4 col-lg-3">{translate text='Format'}</div>
						<div class="result-value col-tn-8 col-lg-9">{implode subject=$record->getFormats()}</div>
					</div>

					{if $displayItemBarcode == 1}
						{if !empty($record->barcode)}
							<div class="row">
								<div class="result-label col-tn-4 col-lg-3">{translate text='Barcode'}</div>
								<div class="col-tn-8 col-lg-9 result-value">
									{$record->barcode}
								</div>
							</div>
						{/if}
					{/if}

					{if $showRatings && $record->getGroupedWorkId() && $record->getRatingData()}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Rating'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{include file="GroupedWork/title-rating.tpl" id=$record->getGroupedWorkId() summId=$record->getGroupedWorkId() ratingData=$record->getRatingData() showNotInterested=false}
							</div>
						</div>
					{/if}

					{if $hasLinkedUsers}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Checked Out To'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{$record->getUserName()}
							</div>
						</div>
					{/if}

					{if !empty($record->returnClaim)}
						<div class="row">
							<div class="result-value col-tn-8 col-lg-9 col-tn-offset-4 col-lg-offset-3 return_claim">
								{$record->returnClaim}
							</div>
						</div>
					{else}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Due'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{$record->dueDate|date_format}
								{if $record->isOverdue()}
									&nbsp;<span class="label label-danger">{translate text="OVERDUE"}</span>
								{elseif $record->getDaysUntilDue() == 0}
									&nbsp;<span class="label label-warning">({translate text="Due today"})</span>
								{elseif $record->getDaysUntilDue() == 1}
									&nbsp;<span class="label label-warning"> {translate text="Due tomorrow"})</span>
								{elseif $record->getDaysUntilDue() <= 7}
									&nbsp;<span class="label label-warning">({translate text="Due in %1% days" 1=$record->getDaysUntilDue()})</span>
								{/if}
							</div>
						</div>
					{/if}

					{if !empty($record->fine)}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Fine'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{if $record->fine}
									<span class="overdueLabel"> {translate text="%1% (up to now)" 1=$record->fine} </span>
								{/if}
							</div>
						</div>
					{/if}

					{if empty($record->returnClaim) && ($showRenewed && $record->renewCount || $defaultSortOption == 'renewed')}{* Show times renewed when sorting by that value (even if 0)*}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Renewed'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{if empty($record->maxRenewals)}
									{translate text="%1% times" 1=$record->renewCount}
								{else}
                                    {translate text="%1% of %2% times" 1=$record->renewCount 2=$record->maxRenewals}
								{/if}
							</div>
						</div>
					{/if}

					{if $showWaitList}
						<div class="row">
							<div class="result-label col-tn-4 col-lg-3">{translate text='Wait List'}</div>
							<div class="result-value col-tn-8 col-lg-9">
								{* Wait List goes here *}
								{$record->holdQueueLength}
							</div>
						</div>
					{/if}
				</div>

				{* Actions for Title *}
				{*<div class="{if $showCovers}col-xs-9 col-sm-8 col-md-4 col-lg-3{else}col-xs-11{/if}">*}
				<div class="col-xs-12 col-md-3">
					<div class="btn-group btn-group-vertical btn-block">
						{if empty($record->returnClaim)}
							{if !isset($record->canRenew) || $record->canRenew == true}
								<a href="#" onclick="return AspenDiscovery.Account.renewTitle('{$record->userId}', '{$record->recordId}', '{$record->renewIndicator}');" class="btn btn-sm btn-primary">{translate text='Renew'}</a>
							{elseif isset($record->autoRenew) && $record->autoRenew == true}
								{if !empty($record->autoRenewError)}
									{$record->autoRenewError}
								{else}
									{translate text='koha_auto_renew_auto' defaultText='If eligible, this item will renew on<br/>%1%' 1=$record->getFormattedRenewalDate()}
								{/if}
							{else}
								{if !empty($record->renewError)}
									{$record->renewError}
								{else}
									{translate text="Sorry, this title cannot be renewed"}
								{/if}
							{/if}
						{/if}
					</div>
					{if $showWhileYouWait}
						<div class="btn-group btn-group-vertical btn-block">
							{if !empty($record->getGroupedWorkId())}
								<button onclick="return AspenDiscovery.GroupedWork.getYouMightAlsoLike('{$record->getGroupedWorkId()}');" class="btn btn-sm btn-default btn-wrap">{translate text="You Might Also Like"}</button>
							{/if}
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}