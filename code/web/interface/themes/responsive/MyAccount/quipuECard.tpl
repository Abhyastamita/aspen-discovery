{strip}
<h1>{translate text='Register for a Library Card' isPublicFacing=true}</h1>
<div class="page">
	{if !empty($eCardSettings)}
		{if !empty($selfRegistrationFormMessage)}
			<div id="selfRegistrationMessage">
				{translate text=$selfRegistrationFormMessage isPublicFacing=true isAdminEnteredData=true}
			</div>
		{/if}

		<div id="eCardParent">
			<!-- The following script tags can be placed in the library's <head> or <body> tag -->
			<script src="https://{$eCardSettings->server}/js/eCARDEmbed.js"></script>
			<script>
				$(document).ready(function () {ldelim}
					loadQGeCARD({$eCardSettings->clientId});
				{rdelim});
			</script>

			<!-- The following <div> tag should be placed on the web page where you the library would like the registration form to display -->
			<div id="eCARD" data-language="{$userLang->code}" data-branchid=""></div>
		</div>
	{else}
		{translate text="eCARD functionality is not properly configured." isPublicFacing=true}
	{/if}
</div>
{/strip}