<?php
/*
Plugin Name: UberMenu - Flat Skins Pack Extension
Plugin URI: http://wpmegamenu.com/flat-skins
Description: A set of 30 flat-style skins for UberMenu
Version: 3.0
Author: Chris Mavricos, SevenSpark
Author URI: http://sevenspark.com
License: You should have purchased a license from http://codecanyon.net/user/sevenspark/portfolio?ref=sevenspark
*/

/*
Copyright 2013-2014  Chris Mavricos, SevenSpark http://sevenspark.com (email : chris@sevenspark.com) 
*/

function ubermenu_skins_flat_register_ubermenu_skins(){
	
	if( function_exists( 'ubermenu_register_skin' ) ){
		$plugin_url = plugin_dir_url( __FILE__ );

		$skins = array(

			'blue ' => array(
				'title'		=> '--- Flat Light Submenus ---',
			),

			//Black Bar - Light Sub
			'black' => array(
				'title'		=> 'Flat - Black Bar + Black',
			),
			'green' => array(
				'title'		=> 'Flat - Black Bar + Green',
			),
			'blue' => array(
				'title'		=> 'Flat - Black Bar + Blue',
			),
			'tomato' => array(
				'title'		=> 'Flat - Black Bar + Tomato',
			),
			'purple' => array(
				'title'		=> 'Flat - Black Bar + Purple',
			),
			'pink' => array(
				'title'		=> 'Flat - Black Bar + Pink',
			),
			'aqua' => array(
				'title'		=> 'Flat - Black Bar + Aqua',
			),
			'red' => array(
				'title'		=> 'Flat - Black Bar + Red',
			),


			//Colored Bar - Light Sub
			'green-bar' => array(
				'title'		=> 'Flat - Green Bar',
			),
			'blue-bar' => array(
				'title'		=> 'Flat - Blue Bar',
			),
			'tomato-bar' => array(
				'title'		=> 'Flat - Tomato Bar',
			),
			'purple-bar' => array(
				'title'		=> 'Flat - Purple Bar',
			),
			'pink-bar' => array(
				'title'		=> 'Flat - Pink Bar',
			),
			'aqua-bar' => array(
				'title'		=> 'Flat - Aqua Bar',
			),
			'red-bar' => array(
				'title'		=> 'Flat - Red Bar',
			),


			//Spacer
			'black-dark  '=> array(
				'title'		=> '',
			),

			'blue-dark ' => array(
				'title'		=> '--- Flat Dark Submenus ---',
			),

			//Black Bar - Dark Sub
			'black-dark' => array(
				'title'		=> 'Flat - Black Bar + Black [Dark Submenu]',
			),
			'green-dark' => array(
				'title'		=> 'Flat - Black Bar + Green [Dark Submenu]',
			),
			'blue-dark' => array(
				'title'		=> 'Flat - Black Bar + Blue [Dark Submenu]',
			),
			'tomato-dark' => array(
				'title'		=> 'Flat - Black Bar + Tomato [Dark Submenu]',
			),
			'purple-dark' => array(
				'title'		=> 'Flat - Black Bar + Purple [Dark Submenu]',
			),
			'pink-dark' => array(
				'title'		=> 'Flat - Black Bar + Pink [Dark Submenu]',
			),
			'aqua-dark' => array(
				'title'		=> 'Flat - Black Bar + Aqua [Dark Submenu]',
			),
			'red-dark' => array(
				'title'		=> 'Flat - Black Bar + Red [Dark Submenu]',
			),

			//Colored Bar - Dark Sub
			'green-bar-dark' => array(
				'title'		=> 'Flat - Green Bar [Dark Submenu]',
			),
			'blue-bar-dark' => array(
				'title'		=> 'Flat - Blue Bar [Dark Submenu]',
			),
			'tomato-bar-dark' => array(
				'title'		=> 'Flat - Tomato Bar [Dark Submenu]',
			),
			'purple-bar-dark' => array(
				'title'		=> 'Flat - Purple Bar [Dark Submenu]',
			),
			'pink-bar-dark' => array(
				'title'		=> 'Flat - Pink Bar [Dark Submenu]',
			),
			'aqua-bar-dark' => array(
				'title'		=> 'Flat - Aqua Bar [Dark Submenu]',
			),
			'red-bar-dark' => array(
				'title'		=> 'Flat - Red Bar [Dark Submenu]',
			),


			//Spacer
			'blue  '=> array(
				'title'		=> '',
			),
		);

		foreach( $skins as $id => $props ){
			ubermenu_register_skin( 'flat-'.$id , $props['title'] , $plugin_url.'skins/'.$id.'.css' );
		}

	}
} 

add_action( 'init', 'ubermenu_skins_flat_register_ubermenu_skins' , 5 , 0 );