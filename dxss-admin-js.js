var lists = [];
lists.push( Array('...', '#', 'favicon' ));
lists.push( Array('Twitter', 'http://twitter.com/home?status=%ts {surl}', 'favicon') );
lists.push( Array('Facebook', 'http://www.facebook.com/sharer.php?t=%s&u={url}', 'favicon') );
lists.push( Array('Wikipedia (en)', 'http://en.wikipedia.org/w/index.php?title=Special:Search&search=%s', 'favicon') );
lists.push( Array('Google Maps', 'http://maps.google.com/?q=%s', 'favicon') );
lists.push( Array('Google Reader', 'http://www.google.com/reader/link?title={title}&snippet=%s&url={url}', 'http://www.google.com/reader/ui/favicon.ico') );
lists.push( Array('Google Buzz', 'http://www.google.com/buzz/post?url={url}', 'http://www.google.com/intl/da_ALL/mobile/buzz/icon.png') );
lists.push( Array('Email', 'mailto:?subject={title}&amp;body=%s - {url}', 'http://mail.google.com/favicon.ico') );
lists.push( Array('Print', 'http://www.printfriendly.com/print?url={url}', 'http://www.printfriendly.com/images/printfriendly.ico') );
lists.push( Array('Digg', 'http://digg.com/submit?phase=2&amp;url={url}&amp;title={title}&amp;bodytext=%s', 'favicon') );
lists.push( Array('Blogger', 'http://www.blogger.com/blog_this.pyra?t&u={url}&n={title}&pli=1', 'favicon') );
lists.push( Array('LinkedIn', 'http://www.linkedin.com/shareArticle?mini=0&url={url}&title={title}&summary=%s', 'favicon') );
lists.push( Array('Orkut', 'http://promote.orkut.com/preview?nt=orkut.com&amp;tt={title}&amp;du={url}&amp;cn=%s', 'http://orkut.com/favicon.ico') );
lists.push( Array('Tumblr', 'http://www.tumblr.com/share?v=3&amp;u={url}&amp;t={title}&amp;s=%s', 'favicon') );
lists.push( Array('Posterous', 'http://posterous.com/share?linkto={url}&title={title}&selection=%s', 'http://posterous.com/images/favicon.png') );

$j = jQuery.noConflict();

$j(document).ready(function(){

	// Basic Admin Functions

	$j('#colorpicker').hide();
	$j('.helpWindow, .wpsrBox').hide();
	
	$j('.message').append('<span class="close">x</span>');
	
	$j('.message .close').click(function(){
		$j(this).parent().slideUp();
	});
	
	for(i=0; i<lists.length; i++){
		$j('#addList').append('<option value="' + i + '">' + lists[i][0] + '</option>');
	}
	$j('#addList').append('<option value="moreButtons">More buttons ...</option>');
	
	
	$j('#addList').change(function(){
		if($j('#addList').val() == 'moreButtons'){
			$j('.wpsrBox').fadeIn();
			$j('#dxss_list_search').focus();
		}else{
			val = $j('#dxss_lists').val() + "\n" + lists[$j(this).val()];
			$j('#dxss_lists').val(val);
		}
	});
	
	$j('#addCustom').click(function(){
		customName = prompt('Enter the name of the button. Eg: Google, Wikipedia');
		customUrl = prompt('Enter the Share URL of the site. Use %s in the URL for the selected text. See help for more terms', 'http://');
		customIcon = prompt('Enter the Icon URL. Use "favicon" to automatically get the Icon', 'favicon');
		
		if(customName != null){
			val = $j('#dxss_lists').val() + "\n" + customName + ',' + customUrl + ',' + customIcon;
			$j('#dxss_lists').val(val);
		}
		
	});
	
	$j('#addSearch').click(function(){
		searchName = prompt('Enter the name of the button. Eg: Search my blog');
		searchUrl = prompt('Enter the Search URL of your site. You can also use your google adsense search URL eg:http://domain.com/?s=%s', 'http://');
		searchIcon = prompt('Enter the Icon URL. Use "favicon" to automatically get the Icon', 'favicon');
		
		if(searchName != null){
			val = $j('#dxss_lists').val() + "\n" + searchName + ',' + searchUrl + ',' + searchIcon;
			$j('#dxss_lists').val(val);
		}
	});
	
	$j('.closeHelp, .openHelp').toggle(function(){
		$j('.helpWindow').fadeIn();
	},function(){
		$j('.helpWindow').fadeOut();
	});
	
	$j('.closeLinks, .openWpsrLinks').toggle(function(){
		$j('.wpsrBox').fadeIn();
		$j('#dxss_list_search').focus();
	},function(){
		$j('.wpsrBox').fadeOut();
	});
	
	var f = $j.farbtastic('#colorpicker');
	$j('.color').each(function(){
		f.linkTo(this);
	}).focus(function(){
        f.linkTo(this);
	});

	$j('.color').focus(function(){
		$j('#colorpicker').fadeIn();
	});
	 
	 $j('.color').blur(function(){
		$j('#colorpicker').fadeOut();
	});
	 
	// Live search
	$j('#dxss_list_search').keyup(function(event){
		var search_text = $j('#dxss_list_search').val();
		var rg = new RegExp(search_text,'i');
		$j('.dxss_wpsr_sites li').each(function(){
			if($j.trim($j(this).text()).search(rg) == -1) {
				$j(this).css('display', 'none');
			}	
			else {
				$j(this).css('display', '');
			}
		});
	});
	
	$j('.dxss_wpsr_sites a').click(function(){
		val = $j('#dxss_lists').val() + "\n" + $j(this).text() + ',' + $j(this).attr('rel') + ',' + 'favicon';
		$j('#dxss_lists').val(val);
		$j(this).after('<span class="addedInfo">  Added !</span>');
		$j('.addedInfo').fadeOut('100');
	});
	
	$j('.preview').hover(function(){
		listVal = $j('#dxss_lists').val();
		listsFinal = listVal.split("\n").join('|');
		$j('.preview').selectedTextSharer({
			title : $j('input[name="dxss_title"]').val(),
            lists : listsFinal,
			truncateChars : $j('input[name=dxss_truncateChars]').val(),
			extraClass : $j('input[name=dxss_extraClass]').val(),
			borderColor : $j('input[name=dxss_borderColor]').val(),
			background : $j('input[name=dxss_bgColor]').val(),
			titleColor : $j('input[name=dxss_titleColor]').val(),
			hoverColor : $j('input[name=dxss_hoverColor]').val(),
			textColor : $j('input[name=dxss_textColor]').val()
		});
	});
});