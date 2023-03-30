<?php
/**
 * Plugin Name:       OpenLab Activity Block
 * Plugin URI:        https://openlab.citytech.cuny.edu/
 * Description:       Add Gutenberg block for displaying BP activity stream and register legacy widget.
 * Version:           1.0.0
 * Author:            OpenLab
 * Author URI:        https://openlab.citytech.cuny.edu/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */


function ol_activity_block_init() {
    $asset_file = require_once plugin_dir_path( __FILE__ ) . '/block/build/index.asset.php';
    require_once plugin_dir_path( __FILE__ ) . '/block/src/render.php';
    
    // Register JS script
    wp_register_script(
        'ol-activity-block',
        plugins_url( '/block/build/index.js', __FILE__ ),
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Register CSS
    wp_register_style(
        'ol-activity-block',
        plugins_url( '/block/build/index.css', __FILE__ ),
        array(),
        $asset_file['version']
    );

    // Register block
    register_block_type(
        'openlab/activity-block', [
            'api_version'       => 2,
            'editor_script'     => 'ol-activity-block',
            'editor_style'      => 'ol-activity-block',
            'attributes'        => array(
                'displayStyle'  => [
                    'type'      => 'string',
                    'default'   => 'full'
                ],
                'numItems'      => [
                    'type'      => 'integer',
                    'default'   => 5
                ],
                'activities'    => [
                    'type'      => 'array',
                    'source'    => 'string'
                ]
            ),
            'render_callback'   => 'openlab_render_activity_block'
        ]
    );

	wp_enqueue_style(
		'ol-activity-block-public',
		plugins_url( '/assets/public.css', __FILE__ ),
		array(),
		'1.0.0'
	);
}
add_action( 'bp_init', 'ol_activity_block_init' );

function ol_activity_block_widget() {
    register_widget( 'Openlab_Activity_Block_Widget' );
}
add_action( 'widgets_init', 'ol_activity_block_widget' );

require_once plugin_dir_path( __FILE__ ) . '/includes/helpers.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/widget.php';
