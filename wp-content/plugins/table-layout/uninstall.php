<?php if ( ! defined( 'ABSPATH' ) ) exit; //exits when accessed directly

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
	return;
}

delete_option( 'mmtl_version' );
delete_option( 'mmtl_icon_classes' );

delete_post_meta_by_key( 'mmtl_active' );

?>