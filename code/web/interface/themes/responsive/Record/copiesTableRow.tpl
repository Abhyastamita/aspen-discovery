{strip}
	{* resize the columns when  including the lastcheckin box
 xs-5 : 41.6667%
 xs-4 : 33.3333%  (1/3)
 xs-3 : 25%       (1/4)
 xs-2 : 16.6667% (1/6)
 *}
	<tr class="{if !empty($hiddenCopy)} hiddenCopy{/if}"{if !empty($hiddenCopy)} style="display: none"{/if}>
		{if !empty($showVolume)}
			<td>
				{if !empty($holding.volume)}
					<span title="Volume">{$holding.volume}</span>
				{/if}
			</td>
		{/if}
		<td>
			<strong>
				{$holding.shelfLocation|escape}
				{if !empty($holding.locationLink)} (<a href='{$holding.locationLink}' target="_blank">Map</a>){/if}
			</strong>
		</td>
		{if $showFormatInHoldings}
			<td>
				{$holding.format}
			</td>
		{/if}
		<td class="holdingsCallNumber">
			{$holding.callNumber|escape}
			{if !empty($holding.link)}
				{foreach from=$holding.link item=link}
					<a href='{$link.link}' target="_blank">{$link.linkText}</a><br>
				{/foreach}
			{/if}
		</td>
		{if !empty($hasNote)}
			<td>
				{if !empty($holding.note)}{$holding.note}{/if}
			</td>
		{/if}
		<td >
			{if !empty($holding.reserve) && $holding.reserve == "Y"}
				{translate text="On Reserve - Ask at Circulation Desk" isPublicFacing=true}
			{else}
				<span class="{if !empty($holding.availability)}available{else}checkedout{/if}">
					{if $holding.onOrderCopies > 1}{$holding.onOrderCopies}&nbsp;{/if}
					{translate text=$holding.statusFull isPublicFacing=true}{if $holding.holdable == 0 && $showHoldButton} <label class='notHoldable' title='{if !empty($holding.nonHoldableReason)}{$holding.nonHoldableReason}{/if}'>(Not Holdable)</label>{/if}
				</span>
			{/if}
		</td>
		{if !empty($hasDueDate) && $showItemDueDates}
			<td>
				{if !empty($holding.dueDate)}{$holding.dueDate|date_format:"%B %e, %Y"}{/if}
			</td>
		{/if}
		{if !empty($showLastCheckIn)}
			<td>
				{if !empty($holding.lastCheckinDate) && $holding.available}
					{* for debugging: *}
					{*{$holding.lastCheckinDate}<br>*}
					{*{$holding.lastCheckinDate|date_format}<br>*}

					<span title="Last Check-in Date">{$holding.lastCheckinDate|date_format:"%B %e, %Y"}</span>
				{/if}
			</td>
		{/if}

		{if $holdingsHaveUrls}
			<td>
				{if !empty($holding.itemUrl)}<a href="{$holding.itemUrl}" target="_blank"><i class="fas fa-external-link-alt" role="presentation"></i> {if empty($holding.itemUrlDescription)}{$holding.itemUrl}{else}{$holding.itemUrlDescription}{/if}</a> {/if}
			</td>
		{/if}
	</tr>
{/strip}