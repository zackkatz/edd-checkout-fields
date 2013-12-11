<?php
global $wp_locale, $post, $edd_options, $current_user;
$pagenow = 'download';
$typenow = 'download';
$adminpage = 'post-new-php';
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/post.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php'); 
$post_type='download';
wp_enqueue_script('post');
if ( wp_is_mobile() ){
	wp_enqueue_script( 'jquery-touch-punch' );
}
$user_ID = $current_user->ID;
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';
if (EDD_FES()->vendors->is_s3_active()){
	add_thickbox();
}
wp_enqueue_media( array( 'post' => $post_ID ) );
wp_enqueue_script( 'jquery-validation', EDD_PLUGIN_URL . 'assets/js/jquery.validate.min.js',array(), fes_plugin_version);
wp_enqueue_script( 'edd-fes-js', fes_assets_url . 'js/fes_adf.js', array( 'jquery', 'jquery-validation' ), fes_plugin_version );
wp_enqueue_script( 'media-upload' );
wp_enqueue_script( 'thickbox' );
wp_localize_script( 'edd-fes-js', 'EDDFESL10n', array(
	'oneoption' => __( 'At least one price option is required.', 'edd_fes' ),
	'post_id'            => 0,
	'edd_version'        => EDD_VERSION,
	'add_new_download'   => __( 'Add New Download', 'edd' ), 									// Thickbox title
	'use_this_file'      => __( 'Use This File','edd' ), 										// "use this file" button
	'one_price_min'      => __( 'You must have at least one price', 'edd' ),
	'one_file_min'       => __( 'You must have at least one file', 'edd' ),
	'one_field_min'      => __( 'You must have at least one field', 'edd' ),
	'currency_sign'      => edd_currency_filter(''),
	'currency_pos'       => isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before',
	'new_media_ui'       => apply_filters( 'edd_use_35_media_ui', 2 ),
	'remove_text'        => __( 'Remove', 'edd' ),
	'admin_ajax_url'     => admin_url(),
));
if (EDD_FES()->vendors->is_s3_active()){
wp_enqueue_style( 'thickbox' ); 
}
?>
<script type="text/javascript">
	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
	var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' );?>',
		pagenow = '<?php echo $pagenow;?>',
		typenow = '<?php echo $typenow;?>',
		adminpage = '<?php echo $adminpage;?>',
		thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] );?>',
		decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] );?>',
		isRtl = "<?php echo(int) is_rtl();?>";
		var post = 0;
			/* <![CDATA[ */	
			var serverurl = "<?php echo esc_url_raw(get_option('siteurl'));?>";
			var userSettings = {"url":"<?php echo esc_url_raw(get_option('siteurl'));?>",
								"uid":"<?php echo get_current_user_id();?>",
								"time":"<?php echo time();?>"};
		
			var commonL10n = {"warnDelete":"You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."};
			var wpAjax = {"noPerm":"You do not have permission to do that.","broken":"An unidentified error has occurred."};var autosaveL10n = {"autosaveInterval":"60","savingText":"Saving Draft\u2026","saveAlert":"The changes you made will be lost if you navigate away from this page."};
			var postL10n = {"ok":"OK","cancel":"Cancel","publishOn":"Publish on:","publishOnFuture":"Schedule for:","publishOnPast":"Published on:","showcomm":"Show more comments","endcomm":"No more comments found.","publish":"Publish","schedule":"Schedule","update":"Update","savePending":"Save as Pending","saveDraft":"Save Draft","private":"Private","public":"Public","publicSticky":"Public, Sticky","password":"Password Protected","privatelyPublished":"Privately Published","published":"Published","comma":","};
			var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"<?php echo addslashes(get_option('siteurl'));?>/wp-includes\/js\/thickbox\/loadingAnimation.gif","closeImage":"<?php echo addslashes(get_option('siteurl'));?>/wp-includes\/js\/thickbox\/tb-close.png"};
			var _wpMediaModelsL10n = {"settings":{"ajaxurl":"\/wnwplugins\/wp-admin\/admin-ajax.php","post":{"id":0}}};
			var pluploadL10n = {"queue_limit_exceeded":"You have attempted to queue too many files.","file_exceeds_size_limit":"%s exceeds the maximum upload size for this site.","zero_byte_file":"This file is empty. Please try another.","invalid_filetype":"This file type is not allowed. Please try another.","not_an_image":"This file is not an image. Please try another.","image_memory_exceeded":"Memory exceeded. Please try another smaller file.","image_dimensions_exceeded":"This is larger than the maximum size. Please try another.","default_error":"An error occurred in the upload. Please try again later.","missing_upload_url":"There was a configuration error. Please contact the server administrator.","upload_limit_exceeded":"You may only upload 1 file.","http_error":"HTTP error.","upload_failed":"Upload failed.","big_upload_failed":"Please try uploading this file with the %1$sbrowser uploader%2$s.","big_upload_queued":"%s exceeds the maximum upload size for the multi-file uploader when used in your browser.","io_error":"IO error.","security_error":"Security error.","file_cancelled":"File canceled.","upload_stopped":"Upload stopped.","dismiss":"Dismiss","crunching":"Crunching\u2026","deleted":"moved to the trash.","error_uploading":"\u201c%s\u201d has failed to upload."};
			var _wpPluploadSettings = {"defaults":{"runtimes":"html5,silverlight,flash,html4","file_data_name":"async-upload","multiple_queues":true,"max_file_size":"1048576000b","url":"\/wnwplugins\/wp-admin\/async-upload.php","flash_swf_url":"<?php echo addslashes(get_option('siteurl'));?>/wp-includes\/js\/plupload\/plupload.flash.swf","silverlight_xap_url":"<?php echo addslashes(get_option('siteurl'));?>/wp-includes\/js\/plupload\/plupload.silverlight.xap","filters":[{"title":"Allowed Files","extensions":"*"}],"multipart":true,"urlstream_upload":true,"multipart_params":{"action":"upload-attachment","_wpnonce":"8601b40fde"}},"browser":{"mobile":false,"supported":true},"limitExceeded":false};
			var _wpMediaViewsL10n = {"url":"URL","addMedia":"Add Media","search":"Search","select":"Select","cancel":"Cancel","selected":"%d selected","dragInfo":"Drag and drop to reorder images.","uploadFilesTitle":"Upload Files","uploadImagesTitle":"Upload Images","mediaLibraryTitle":"Media Library","insertMediaTitle":"Insert Media","createNewGallery":"Create a new gallery","returnToLibrary":"\u2190 Return to library","allMediaItems":"All media items","noItemsFound":"No items found.","insertIntoPost":"Insert into post","uploadedToThisPost":"Uploaded to this post","warnDelete":"You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.","insertFromUrlTitle":"Insert from URL","setFeaturedImageTitle":"Set Featured Image","setFeaturedImage":"Set featured image","createGalleryTitle":"Create Gallery","editGalleryTitle":"Edit Gallery","cancelGalleryTitle":"\u2190 Cancel Gallery","insertGallery":"Insert gallery","updateGallery":"Update gallery","addToGallery":"Add to gallery","addToGalleryTitle":"Add to Gallery","reverseOrder":"Reverse order","settings":{"tabs":[],"tabUrl":"<?php echo addslashes(get_option('siteurl'));?>/wp-admin\/media-upload.php?chromeless=1","mimeTypes":{"image":"Images","audio":"Audio","video":"Video"},"captions":true,"nonce":{"sendToEditor":"5ec5c856af"},"post":{"id":69,"nonce":"67ffdf9e8c","featuredImageId":-1}}};
			var _wpMediaViewsL10n = {"url":"URL","addMedia":"Add Media","search":"Search","select":"Select","cancel":"Cancel","selected":"%d selected","dragInfo":"Drag and drop to reorder images.","uploadFilesTitle":"Upload Files","uploadImagesTitle":"Upload Images","mediaLibraryTitle":"Media Library","insertMediaTitle":"Insert Media","createNewGallery":"Create a new gallery","returnToLibrary":"\u2190 Return to library","allMediaItems":"All media items","noItemsFound":"No items found.","insertIntoPost":"Insert into post","uploadedToThisPost":"Uploaded to this post","warnDelete":"You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.","insertFromUrlTitle":"Insert from URL","setFeaturedImageTitle":"Set Featured Image","setFeaturedImage":"Set featured image","createGalleryTitle":"Create Gallery","editGalleryTitle":"Edit Gallery","cancelGalleryTitle":"\u2190 Cancel Gallery","insertGallery":"Insert gallery","updateGallery":"Update gallery","addToGallery":"Add to gallery","addToGalleryTitle":"Add to Gallery","reverseOrder":"Reverse order","settings":{"tabs":[],"tabUrl":"<?php echo addslashes(get_option('siteurl'));?>/wp-admin\/media-upload.php?chromeless=1","mimeTypes":{"image":"Images","audio":"Audio","video":"Video"},"captions":true,"nonce":{"sendToEditor":"5ec5c856af"},"post":{"id":69,"nonce":"67ffdf9e8c","featuredImageId":-1}}};
			var wordCountL10n = {"type":"w"};
			var quicktagsL10n = {"wordLookup":"Enter a word to look up:","dictionaryLookup":"Dictionary lookup","lookup":"lookup","closeAllOpenTags":"Close all open tags","closeTags":"close tags","enterURL":"Enter the URL","enterImageURL":"Enter the URL of the image","enterImageDescription":"Enter a description of the image","fullscreen":"fullscreen","toggleFullscreen":"Toggle fullscreen mode","textdirection":"text direction","toggleTextdirection":"Toggle Editor Text Direction"};
			var wpLinkL10n = {"title":"Insert\/edit link","update":"Update","save":"Add Link","noTitle":"(no title)","noMatchesFound":"No matches found."};
			
			var inlineEditL10n = {"error":"Error while saving the changes.","ntdeltitle":"Remove From Bulk Edit","notitle":"(no title)","comma":","};
			/* ]]> */
</script>
<script type='text/javascript' src='<?php echo trailingslashit(get_option('siteurl'));?>wp-admin/load-scripts.php?c=1&load%5B%5D=jquery-core,jquery-migrate,utils,plupload,plupload-html5,plupload-flash,plupload-silverlight,plupload-html4,json2&ver=3.7.1'></script>
<script type='text/javascript' src='<?php echo trailingslashit(get_option('siteurl'));?>wp-admin/load-scripts.php?c=1&load%5B%5D=jquery-core,jquery-migrate,utils,plupload,plupload-html5,plupload-flash,plupload-silverlight,plupload-html4,json2&ver=3.7.1'></script>