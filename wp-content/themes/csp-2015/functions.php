<?php

	// Increase post/media size:
	@ini_set( 'upload_max_size' , '64M' );
	@ini_set( 'post_max_size', '64M');
	@ini_set( 'max_execution_time', '300' );

	// Remove the annoying admin bar:
	add_action('get_header', 'remove_admin_login_header');
	function remove_admin_login_header() {
		remove_action('wp_head', '_admin_bar_bump_cb');
	}

	// Basic Panel Shortcode:
	function panel_shortcode( $atts, $content = null ) {
		
		// Default values:
		$a = shortcode_atts( array(
			'icon' => '',
		), $atts );
		
		if($a['icon'] !== '') {
			// Return side version
			return '<div class="panel c cf">
				<div class="d33 full featurette">
				<i class="fa fa-' .$a['icon']. '"></i>
				
				</div>
				<div class="d66 full"><p>'. do_shortcode($content). '</p></div>
			</div>';
		}
		else {
			return '<div class="panel c"><p>' . do_shortcode($content) . '</p></div>';			
		}

	}
	add_shortcode( 'panel', 'panel_shortcode' );

	
	// Title Shortcode:
	function title_shortcode( $atts, $content = null ) {
		return '<h2 class="m2">' . $content . '</h2>';
	}
	add_shortcode( 'title', 'title_shortcode' );
	
	
	// Section Shortcode:
	function section_shortcode( $atts, $content = null) {
		return '<section class="cf">' . do_shortcode($content) . '</section>';
	}
	add_shortcode('section', 'section_shortcode');
	
	// Third / Font Awesome Element Shortcode:
	function triple_shortcode( $atts ) {
	
		// Default values:
		$a = shortcode_atts( array(
			'icon1' => 'check',
			'title1' => 'Title 1',
			'text1' => '',
			'icon2' => 'check',
			'title2' => 'Title 2',
			'text2' => '',
			'icon3' => 'check',
			'title3' => 'Title 3',
			'text3' => ''
		), $atts );
		
		return "<section class='cf'>
			<div class='d33 c'>
				<i class='fa fa-$a[icon1]'></i>
				<h2>$a[title1]</h2>
				<p>$a[text1]</p>
			</div>
			<div class='d33 c'>
				<i class='fa fa-$a[icon2]'></i>
				<h2>$a[title2]</h2>
				<p>$a[text2]</p>
			</div>
			<div class='d33 c'>
				<i class='fa fa-$a[icon3]'></i>
				<h2>$a[title3]</h2>
				<p>$a[text3]</p>
			</div>			
		</section>
		";

	}
	add_shortcode( 'triple', 'triple_shortcode' );
	
	// Photo box element:
	
	function photobox_shortcode( $atts ) {
		
		$a = shortcode_atts(array(
			'pic' => 'etc'
		), $atts);
		
		return '<div class="panel photobox" style="background-image: url('. $a["pic"] .')"></div>';
	}
	add_shortcode('photobox', 'photobox_shortcode');
	
	
	// Feature box Element Shortcode:
	/*
	function featurette_shortcode( $atts, $content = null ) {
		
		$a = shortcode_atts(array(
			'pic' => 'etc'
		), $atts);
		
		return '<div class="panel featurette" style="background-image: url('.$a["pic"] .'); min-height: 45vh;">
			<div class="page-title">
		
		<p>' . do_shortcode($content) . '</p></div></div>';
	}
	add_shortcode( 'featurette', 'featurette_shortcode' );
	*/
		
	
	
	
?>