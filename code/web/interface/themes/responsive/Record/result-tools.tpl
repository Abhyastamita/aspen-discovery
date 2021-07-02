{strip}
<div class="btn-toolbar">
	<div class="btn-group btn-group-vertical btn-block">
		{* actions *}
		{foreach from=$actions item=curAction}
			<a href="{$curAction.url}" {if $curAction.target}target="{$curAction.target}"{/if} {if $curAction.onclick}onclick="{$curAction.onclick}"{/if} class="btn btn-sm {if empty($curAction.btnType)}btn-action{else}{$curAction.btnType}{/if} btn-wrap">{if $curAction.target == "_blank"}<i class="fas fa-external-link-alt"></i> {/if}{$curAction.title}</a>
		{/foreach}
	</div>
</div>
{/strip}