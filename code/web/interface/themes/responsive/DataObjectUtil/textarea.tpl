<textarea name='{$propName}' id='{$propName}' {if !empty($property.rows)}rows='{$property.rows}'{/if} {if !empty($property.cols)}cols='{$property.cols}'{/if} {if !empty($property.description)}title='{$property.description}'{/if} class='form-control {if !empty($property.required) && $objectAction != 'edit'}required{/if}' {if !empty($property.readOnly)}readonly{/if} {if !empty($property.maxLength)}maxlength="{$property.maxLength}" {/if}>{$propValue|escape}</textarea>
{*{if !empty($property.readOnly)}*}
	{if $property.type == 'html' || ($property.type == 'markdown' && $useHtmlEditorRatherThanMarkdown)}
		<script>
		{literal}
		tinymce.init({
			selector: '#{/literal}{$propName}{literal}',
			plugins: 'anchor autolink autoresize autosave code codesample colorpicker contextmenu directionality fullscreen help hr image imagetools insertdatetime link lists media paste preview print save searchreplace spellchecker table textcolor textpattern toc visualblocks visualchars wordcount tinymceEmoji',
			toolbar1: 'code | cut copy paste pastetext spellchecker | undo redo searchreplace | image table hr codesample insertdatetime | link anchor | tinymceEmoji',
			toolbar2: 'bold italic underline strikethrough | formatselect fontselect fontsizeselect forecolor backcolor',
			toolbar3: 'numlist bullist toc | alignleft aligncenter alignright | preview visualblocks fullscreen help',
			toolbar: 'image',
			menubar:'',
	            image_advtab: true,
			images_upload_url: '/WebBuilder/AJAX?method=uploadImageTinyMCE',
			convert_urls: false,
			theme: 'modern',
			valid_elements : '*[*]',
			extended_valid_elements : [
				'*[*]',
				'img[class=img-responsive|*]'
			],
		    emoji_show_groups: false,
	        emoji_show_subgroups: false,
		});
		{/literal}
		</script>
	{elseif $property.type == 'markdown'}
		<script type="text/javascript">
			$(document).ready(function(){ldelim}
				var simplemde{$propName} = new SimpleMDE({ldelim}
					element: $("#{$propName}")[0],
					toolbar: ["heading-1", "heading-2", "heading-3", "heading-smaller", "heading-bigger", "|",
						"bold", "italic", "strikethrough", "|",
						"quote", "unordered-list", "ordered-list", "|",
						"link", "image", {ldelim}name:"uploadImage", action:function (){ldelim} return AspenDiscovery.WebBuilder.getUploadImageForm('{$propName}'){rdelim},className: "fa fa-upload",title: "Upload Image"{rdelim}, "|",
						"preview", "guide"],
				{rdelim});
				AspenDiscovery.WebBuilder.editors['{$propName}'] = simplemde{$propName};
			{rdelim});
		</script>
	{/if}
{*{/if}*}