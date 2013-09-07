<?php
# http://bazaar.launchpad.net/~george-edison55/stackphp/trunk/view/head:/examples/src/config.php

if( !class_exists( 'API' ) ):
require_once 'stackphp/api.php';
require_once 'stackphp/auth.php';
require_once 'stackphp/filestore_cache.php';
endif;

API::$key = 'WfdrC3u7rmAQDwaSRYrw2w((';
Auth::$client_id = 1926;

$se_settings = get_option(SEPW_Widget_Init::$option_name);

# Set the cache we will use
if( !isset( $se_settings['cache'] ) )//!isset($_GET['no_api_cache'] ) )
	API::SetCache( new FilestoreCache( SEPW_Widget_Init::get_instance()->plugin_path.'cache' ) );

