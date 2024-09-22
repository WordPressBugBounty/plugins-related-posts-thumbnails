<?php
/**
 * Structure for Optin Form.
 *
 * @since 4.0.2
 */
?>
<style media="screen">
	#wpwrap {
		background-color: #fdfdfd;
	}
	.admin_page_rpt-optin #wpwrap {
		background-color: #F6F9FF;
	}
	.admin_page_rpt-optin #wpbody-content{
		display: flex;
		flex-direction: column;
	}
	.rpt-alert-notice{
		order: -1;
	}
	.admin_page_rpt-optin #wpbody-content .rpt-header-wrapper{
		order: -2;
		margin: 0 0px 20px 0 !important;
		width: 100% !important;
	}
	#wpcontent {
		padding: 0!important
	}
	#rpt-logo-wrapper {
		padding: 10px 0;
		width: 80%;
		margin: 0 auto;
		border-bottom: solid 1px #d5d5d5
	}
	#rpt-logo-wrapper-inner {
		max-width: 600px;
		width: 100%;
		margin: auto
	}
	#rpt-splash {
		width: calc(46% - 40px);
		margin: auto;
		/* background-color: #fdfdfd; */
		text-align: center
	}
	.admin_page_rpt-optin #wpbody-content form #rpt-splash{
		max-width: 680px;
	}
	#rpt-splash {
		margin-top: 40px;
	}
	#rpt-splash h1 {

		margin-bottom: 15px;
		font-size: 26px;
		line-height: 32px;
		color: black;
		font-family: "Poppins", sans-serif;
		font-weight: 600;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	#rpt-splash-main {
		padding-bottom: 0
	}
	#rpt-splash-permissions-toggle {
		font-size: 16px;
		font-weight: 600;
		position: relative;
		color: #3C50E0;
		text-decoration: none;
		padding-right: 13px;
		outline: none !important;
		box-shadow: none;
		text-align: left;
		display: inline-block;
	}
	#rpt-splash-permissions-toggle:after {
		content: "";
		width: 8px;
		height: 8px;
		border-width: 0 2px 2px 0;
		border-style: solid;
		border-color: inherit;
		right: 27px;
		top: 50%;
		transform: rotate(45deg) translateY(-4px);
		display: inline-block;
		margin-left: 6px;
	}
	#rpt-splash-permissions #rpt-splash-permissions-dropdown{
		margin-top: 25px;
	}
	#rpt-splash-permissions-dropdown h3 {
		font-size: 16px;
		margin-bottom: 5px;
		color: #516885;
		font-weight: 700;
		line-height: 24px;
		margin: 0 0 5px;
	}
	#rpt-splash-permissions-dropdown p {
		margin-top: 0;
		font-size: 14px;
		margin-bottom: 25px;
		color: #516885;
	}
	#rpt-splash-permissions-dropdown h3:last-child,
	#rpt-splash-permissions-dropdown p:last-child{
		margin-bottom: 0;
	}
	#rpt-splash-main-text {
		font-size: 16px;
		padding: 0;
		margin: 0;
		color: black;
	}
	#rpt-splash-footer {
		width: 80%;
		padding: 15px 0;
		border: 1px solid #d5d5d5;
		font-size: 10px;
		text-align: center;
		margin-top: 238px;
		margin-left: auto;
		margin-right: auto;
	}
	#rpt-ga-optout-btn {
		background: none!important;
		border: none;
		padding: 0!important;
		font: inherit;
		cursor: pointer;
		margin-bottom: 20px;
		font-size: 14px;
		text-decoration: underline;
		text-decoration-style: Dashed;
		text-underline-position: under;
		color: rgb(92 118 151 / 80%);
	}
	#rpt-ga-optout-btn:hover{
		text-decoration: none;
	}
	#rpt-splash-permissions-toggle:hover{
		text-decoration: none;
	}
	.about-wrap .nav-tab + .nav-tab{
		border-left: 0;
	}
	.about-wrap .nav-tab:focus{
		box-shadow: none;
	}
	#rpt-ga-submit-btn {
		border: 0;
		padding: 15px 20px 15px 20px;
		background-color: #1441d8;
		text-decoration: none;
		color: #fff;
		font-size: 17px;
		line-height: 24px;
		font-weight: 500;
		font-family: "Poppins", sans-serif;
		border-radius: 5px;
		transition: all 0.3s;
		display: inline-block;
		max-width: 100%;
		cursor: pointer;
		/* z-index: 6; */
		display: inline-block;
		-webkit-appearance: none;
		margin-bottom: 20px;
		min-height: auto;
		white-space: normal;
	}
	#rpt-ga-submit-btn:before {
		content: "";
		width: 216px;
		display: block;
		max-width: 100%;
	}
	#rpt-ga-submit-btn:hover{
		background-color: #5272e1;
	}
	#rpt-ga-submit-btn:after{
		content: '\279C';
	}
	.rpt-splash-box {
		width: 100%;
		max-width: 600px;
		background-color: #fff;
		border: solid 1px #d5d5d5;
		margin: auto;
		margin-bottom: 20px;
		text-align: center;
		padding: 15px
	}
	.about-wrap .nav-tab{
		height: auto;
		float: none;
		display: inline-block;
		margin-right: 0;
		margin-left: 0;
		font-size: 18px;
		width: 33.333%;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
		padding: 8px 15px;
	}
	.step-wrapper .rpt-splash-box{
		padding: 0;
		border: 0;
		margin-top: 20px;
		margin-bottom: 0;
		text-align: left;
	}
	.nav-tab-wrapper{
		margin:0;
		font-size: 0;
	}
	.nav-tab-wrapper, .wrap h2.nav-tab-wrapper{
		margin:0;
		font-size: 0;
	}
	.rpt-tab-content{
		display: none;
		border:1px solid #d5d5d5;
		padding:1px 20px 20px;
		border-top: 0;
	}
	.rpt-tab-content.active{
		display: block;
	}
	.rpt-seprator{
		border:0;
		border-top: 1px solid #ccc;
		margin: 50px 0;
	}
	#wpbody{
		padding-right: 0;
	}
	#rpt-splash{
		max-width: calc(100% - 64px);
		/* background: #f1f1f1; */
	}
	.rpt-splash-box{
		max-width: 100%;
		box-sizing: border-box;
		overflow: hidden;
	}
	.about-wrap {
		position: relative;
		margin: 25px 35px 0 35px;
		max-width: 80%;
		font-size: 15px;
		width: calc(100% - 64px);
		margin: 0 auto;
	}
	.rpt-left-screenshot{
		float: left;
	}
	.about-wrap p{
		font-size: 14px;
	}
	.rpt-text-settings h5{
		margin: 25px 0 5px;
	}
	.about-wrap .about-description, .about-wrap .about-text{
		font-size: 16px;
	}
	.about-wrap .feature-section h4,.about-wrap .changelog h3{
		font-size: 1em;
	}
	h5{
		font-size: 1em;
	}
	.about-wrap .feature-section img.rpt-left-screenshot{
		margin-left: 0 !important;
		margin-right: 30px !important;
	}
	.about-wrap img{
		width: 50%;
	}
	.rpt-text-settings{
		overflow: hidden;
	}
	#rpt-splash-footer{
		margin-top: 50px;
	}
	.step-wrapper{
		width: 100%;
		transition: all 0.3s ease-in-out;
		-webkit-transition: all 0.3s ease-in-out;
	}
	/*.step-wrapper.slide{
		-webkit-transform: translateX(-50%);
		transform: translateX(-50%);
	}*/
	.step-wrapper:after{
		content: '';
		display: table;
		clear: both;
	}
	.step{
		width: 100%;
		float: left;
		padding: 0 20px;
		box-sizing: border-box;
	}

	.admin_page_rpt-optin #wpbody-content form .step{
		padding-left: 0;
		padding-right: 0;
	}
	.rpt-welcome-screenshots{
		margin-left: 30px !important;
	}
	#rpt-splash-footer{
		font-size: 12px;
	}
	.about-wrap .changelog.rpt-backend-settings{
		margin-bottom: 20px;
	}
	.rpt-backend-settings .feature-section{
		padding-bottom: 20px;
	}
	a.rpt-ga-button.button.button-primary{
		height: auto !important;
	}
	.changelog:last-child{
		margin-bottom: 0;
	}
	.changelog:last-child .feature-section{
		padding-bottom: 0;
	}
	#rpt-logo-text{
		position: relative;
		bottom: 0px;
		max-width: 90px;
		vertical-align: middle;
	}
	.about-wrap .rpt-badge {
		position: absolute;
		top: 0;
		right: 0;
	}
	.rpt-welcome-screenshots {
		float: right;
		margin-left: 10px !important;
		border:1px solid #ccc;
		padding:0;
		box-shadow:4px 4px 0px rgba(0,0,0,.05)
	}
	.about-wrap .feature-section {
		margin-top: 20px;
	}
	.about-wrap .feature-section p{
		max-width: none !important;
	}
	.rpt-welcome-settings{
		clear: both;
		padding-top: 20px;
	}
	.rpt-left-screenshot {
		float: left !important;
	}

	#rpt-splash-main{
		background-color: #fff;
		border: 2px solid #999797;
		-webkit-border-radius: 8px;
		-moz-border-radius: 8px;
		-ms-border-radius: 8px;
		-o-border-radius: 8px;
		border-radius: 8px;
		min-height: 320px;
		margin: 0 auto;
		padding: 30px 30px 30px 30px;
		display: flex;
		align-items: center;
		text-align: left;
	}

	@media only screen and (max-width: 767px) {
		#rpt-splash-main{
			padding: 20px;
		}
		#rpt-ga-submit-btn {
			padding: 15px 20px 15px 20px;
			font-size: 15px;
			line-height: 21px;
		}
		#rpt-ga-submit-btn:before {
			width: 180px;
		}
		#rpt-splash h1 {
			flex-direction: column;
		}
		#rpt-logo-text {
			margin-right: 0;
			bottom: 15px;
		}
	}

	@media only screen and (max-width: 580px) {
		#rpt-splash h1 {
			font-size: 18px;
		}
		#rpt-logo-text {
			max-width: 70px;
		}
	}

	}
</style>
<?php

$user                 = wp_get_current_user();
$name                 = empty( $user->user_firstname ) ? $user->display_name : $user->user_firstname;
$email                = $user->user_email;
$site_link            = '<a href="' . get_site_url() . '">' . get_site_url() . '</a>';
$website              = get_site_url();
$nonce                = wp_create_nonce( 'rpt_submit_optin_nonce' );
$default_rpt_redirect = 'related-posts-thumbnails';
$plugin_title         = 'Related Posts Thumbnails';

/**
 * XSS Attack fix in the opt-in form.
 *
 * @since 1.5.12
 * @version 3.0.0
 */

echo '<form method="post" action="' . admin_url( 'admin.php?page=' . $default_rpt_redirect ) . '">';
echo "<input type='hidden' name='email' value='$email'>";
echo "<input type='hidden' name='rpt_submit_optin_nonce' value='" . sanitize_text_field( $nonce ) . "'>";
echo '<div id="rpt-splash">';
echo '<h1><img id="rpt-logo-text" src="' . plugins_url( 'assets/images/rpt-logo.png', __DIR__ ) . '"></h1>';
echo '<h1>' . esc_html__( $plugin_title, 'related-posts-thumbnails' ) . '</h1>';
echo '<div id="rpt-splash-main" class="rpt-splash-box">';
echo '<div class="step-wrapper">';

echo "<div class='first-step step'>";
echo sprintf( __( '%1$s Hey %2$s,  %4$s If you opt-in some data about your installation of Related Posts Thumbnails will be sent to WPBrigade.com (This doesn\'t include stats) and You will receive new feature updates, security notifications etc. %4$s  %5$sNo Spam, I promise.%6$s %4$s%4$s Help us %7$sImprove Related Posts Thumbnails%8$s %4$s %4$s ', 'related-posts-thumbnails' ), '<p id="rpt-splash-main-text">', '<strong>' . $name . '</strong>', '<strong>' . $website . '</strong>', '<br>', '<i>', '</i>', '<strong>', '</strong>' ) . '</p>';
echo "<button type='submit' id='rpt-ga-submit-btn' class='rpt-ga-button button button-primary' name='rpt-submit-optin' >" . __( 'Allow and Continue ', 'related-posts-thumbnails' ) . '</button><br>';
echo "<button type='submit' id='rpt-ga-optout-btn' name='rpt-submit-optout' >" . __( 'Skip This Step', 'related-posts-thumbnails' ) . '</button>';
echo '<div id="rpt-splash-permissions" class="rpt-splash-box">';
echo '<div id="rpt-splash-permissions-dropdown" style="display: none;">';
echo '<h3>' . __( 'Your Website Overview', 'related-posts-thumbnails' ) . '</h3>';
echo '<p>' . __( 'Your Site URL, WordPress & PHP version, plugins & themes. This data lets us make sure this plugin always stays compatible with the most popular plugins and themes.', 'related-posts-thumbnails' ) . '</p>';

echo '<h3>' . __( 'Your Profile Overview', 'related-posts-thumbnails' ) . '</h3>';
echo '<p>' . __( 'Your name and email address.', 'related-posts-thumbnails' ) . '</p>';

echo '<h3>' . __( 'Admin Notices', 'related-posts-thumbnails' ) . '</h3>';
echo '<p>' . __( 'Updates, Announcement, Marketing. No Spam, I promise.', 'related-posts-thumbnails' ) . '</p>';

echo '<h3>' . __( 'Plugin Actions', 'related-posts-thumbnails' ) . '</h3>';
echo '<p>' . __( "Active, Deactive, Uninstallation and How you use this plugin's features and settings. This is limited to usage data. It does not include any of your sensitive rpt data, such as traffic. This data helps us learn which features are most popular, so we can improve the plugin further.", 'related-posts-thumbnails' ) . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';


echo '</div>';
echo '</div>';
echo '</div>';
echo '</form>';
?>

<script type="text/javascript">
	jQuery(document).ready(function(s) {
		var o = parseInt(s("#rpt-splash-footer").css("margin-top"));
		s("#rpt-splash-permissions-toggle").click(function(a) {
			a.preventDefault(), s("#rpt-splash-permissions-dropdown").toggle(), 1 == s("#rpt-splash-permissions-dropdown:visible").length ? s("#rpt-splash-footer").css("margin-top", o - 208 + "px") : s("#rpt-splash-footer").css("margin-top", o + "px")
		})
	});
</script>
