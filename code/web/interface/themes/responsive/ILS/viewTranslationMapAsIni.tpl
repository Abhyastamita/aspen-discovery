{strip}
	<div id="main-content">
		<h1>{$pageTitleShort}</h1>
		<a class="btn btn-sm btn-default" href='/ILS/TranslationMaps?objectAction=list'><i class="fas fa-arrow-alt-circle-left" role="presentation"></i> {translate text="Return to List" isAdminFacing=true}</a>
		<p>
			{foreach from=$translationMapValues item=translationMapValue}
				{$translationMapValue->value} = {$translationMapValue->translation}<br/>
			{/foreach}
		</p>
	</div>
{/strip}