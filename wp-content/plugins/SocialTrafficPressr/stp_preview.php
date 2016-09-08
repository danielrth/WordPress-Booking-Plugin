<?php
	global $post;
	
	$postId = $post->ID;
	$postTitle = get_post_meta($postId, "stp_facebook_title", true);
	if ( empty($postTitle) ) $postTitle = $post->post_title;
	$postImage = get_post_meta($postId, "stp_facebook_image", true);
	
	if ( empty($postImage) ) {
		$feat_image = wp_get_attachment_url( get_post_thumbnail_id($postId) );

		if ( empty($feat_image) ) {
			$match = array();
			preg_match( "/<img.+src=[\'\"](?P<src>.+?)[\'\"].*>/i", $post->post_content, $match );

			if ( sizeof($match) > 0 ) {
				$postImage = $match["src"];
			}
		}
		else {
			$postImage = $feat_image;
		}
	}
?>
<div class="facebook-previewer">
	<style>
		.facebook-previewer label {
			display: block;
		}
		.facebook-previewer input.form-control {
			width: 100%;
		}
		.hrfp {
			border: 1px solid;
    		border-color: #e5e6e9 #dfe0e4 #d0d1d5;
    		border-radius: 3px;
			
			width: 500px;
			padding: 10px;
		}
		.hrfp-meta-wrapper {
			
		}
		.hrfp .hrfp-avatar {
			float: left;
			margin-right: 5px;
			width: 40px; height: 40px;
		}
		.hrfp .hrfp-avatar img {
			width: 100%;
		}
		.hrfp .hrfp-meta {
			
		}
		.hrfp .hrfp-meta .hrfp-meta-title {
			color: #365899;
			font-size: 14px;
			line-height: 1.38;
			font-weight: bold;
		}
		.hrfp .hrfp-meta .hrfp-meta-content {
			color: #90949c;
			font-size: 12px;
		}
		.hrfp .hrfp-icon {
			float: right;
		}
		.hrfp-color {
			color: #4267b2;
		}
		.hrfp-box {
			border: 1px solid;
			border-color: #e9ebee #dadada #ccc;
			box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.15) inset, 0 1px 4px rgba(0, 0, 0, 0.1);
		}
		.hrfp-box-image {
			height: 250px;
			background-size: cover;
			background-repeat: no-repeat;
		}
		.hrfp-box-wrapper {
			padding: 10px;
		}
		.hrfp-box-title {
			font-family: Georgia, serif;
			font-size: 18px;
		    font-weight: 500;
		    line-height: 22px;
		    margin-bottom: 5px;
		    max-height: 110px;
		    overflow: hidden;
		    word-wrap: break-word;
		}
		.hrfp-box-content {
			line-height: 16px;
    		max-height: 80px;
    		overflow: hidden;
			font-size: 12px;
		}
	</style>
	<script type="text/javascript">
	;(function($) { 
		$(document).ready(function() {
			$("#stp-preview").on('click', function() {
				$(".hrfp-box-title").html(
					$("input[name=fbtitle]").val()
				);
				$(".hrfp-content > p").html(
					$("input[name=fbtitle]").val()
				);

				if ( $(".hrfp-box-image").length > 0 ) { 
					$(".hrfp-box-image").attr('style', "background-image: url('"+$("input[name=fbimage]").val()+"')"); 
				}
				else {
					$(".hrfp-box").prepend('<div class="hrfp-box-image" style="background-image: url('+$("input[name=fbimage]").val()+')"></div>');
				}
			});
			$("#stp-update-meta").on('click', function() {
				location.href = $(this).data('base') + "&fbtitle=" + encodeURIComponent($("input[name=fbtitle]").val()) + "&fbimage=" + encodeURIComponent($("input[name=fbimage]").val());
			});
		});
	})(jQuery);
	</script>
	<p><label>Custom Title For Facebook</label><input name="fbtitle" class="form-control" value="<?php echo $postTitle; ?>"></p>
	<p><label>Custom Image Link For Facebook</label><input name="fbimage" class="form-control" value="<?php echo $postImage; ?>"></p>
	<small>Leave empty for default value. Title is Title of Post, Image is Featured Image or first image of the post by default</small>
	
	<p style="text-align: right; margin-bottom: 5px;">
		<a href="javascript:;" class="button button-default button-large" id="stp-preview">Preview</a>
		<a href="javascript:;" class="button button-primary button-large" id="stp-update-meta" data-base="<?php echo admin_url( 'admin-post.php?action=fb_updatemeta&pid=' . $postId ); ?>">Update Custom Data</a>
	</p>
	
	<div class="hrfp">
		<div class="hrfp-meta-wrapper">
			<div class="hrfp-avatar">
				<img src="<?php echo plugin_dir_url(__FILE__) . "empty-avatar.png" ?>" />
			</div>
			<div class="hrfp-icon">
				<img src="<?php echo plugin_dir_url(__FILE__) . "dropdown.png" ?>" />
			</div>
			<div class="hrfp-meta">
				<div class="hrfp-meta-title">Your Name</div>
				<div class="hrfp-meta-content">Published by <span class="hrfp-color">appname</span> [?] - 1 min</div>
			</div>
		</div>
		<div class="hrfp-content">
			<p><?php echo $postTitle; ?></p>
			
			<div class="hrfp-box">
				<?php if ( ! empty($postImage) ) { ?>
				<div class="hrfp-box-image" style="background-image: url('<?php echo $postImage; ?>')"></div>
				<?php } ?>
				<div class="hrfp-box-wrapper">
					<div class="hrfp-box-title">
						<?php echo $postTitle; ?>
					</div>
					<div class="hrfp-box-content">
						<?php echo get_the_excerpt($postId); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<p style="text-align: right; margin-bottom: 5px;">
		<a href="<?php echo admin_url( 'admin-post.php?action=fb_publish&pid=' . $postId ); ?>" class="button button-primary button-large">Publish to Facebook</a>
	</p>
</div>