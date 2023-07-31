<?php
/**
 * Helper functions.
 *
 * @package openlab-activity-block
 */

/**
 * Modify the output of the activity items.
 *
 * @todo Much of this will not work properly with i18n.
 *
 * @param object $activity      Activity object.
 * @param string $display_style Display style.
 * @return string
 */
function olab_get_activity_action( $activity = null, $display_style = 'full' ) {
	global $activities_template;

	if ( null === $activity ) {
		$activity = $activities_template->activity;
	}

	// Get activity body content.
	$output = $activity->action;

	// Remove link from the user's display name.
	if ( 'simple' === $display_style ) {
		$activity_user_link = bp_core_get_userlink( $activity->user_id );
		$output             = str_replace( $activity_user_link, $activity->display_name, $output );
	}

	// Remove "in the group/forum" text from the activity on the group activity stream.
	if ( bp_is_group() ) {
		$group      = bp_get_group( bp_get_current_group_id() );
		$group_link = bp_get_group_permalink( $group );
		$output     = preg_replace( '/in the group <a href="[^"]+">' . preg_quote( bp_get_group_name(), '/' ) . '<\/a>/', '', $output );
		$output     = str_replace( 'in the forum <a href="' . $group_link . 'forum/">' . bp_get_group_name() . '</a>', '', $output );
		$output     = str_replace( 'in <a href="' . $group_link . '">' . bp_get_group_name() . '</a>', '', $output );
	} elseif ( 'bbp_topic_create' === $activity->type || 'bbp_reply_create' === $activity->type ) {
			$output = str_replace( 'in the forum', 'in the group', $output );
	}

	if ( 'added_group_document' === $activity->type ) {
		$output = str_replace( 'uploaded the file', 'added the file', $output );
	}

	// Create DateTime from the activity date.
	$activity_datetime = new DateTime( $activity->date_recorded );

	// Create TimeZone from the timezone selected in the WP Settings.
	$wp_timezone = new DateTimeZone( wp_timezone_string() );

	// Set timezone to the activity DateTime.
	$activity_datetime->setTimezone( $wp_timezone );

	// Modify activity date format, remove link and add "on" before the date.
	$output .= ' on ' . $activity_datetime->format( 'F d, Y \a\t g:i a' );
	$output  = wpautop( $output );

	// Activity view button.
	$view_button_label = olab_get_activity_button_label( $activity->type );
	$view_button_link  = olab_get_activity_button_url( $activity );

	// Append activity view button.
	if ( $view_button_label ) {
		$output .= '<a href="' . esc_url( $view_button_link ) . '" class="olab-activity-item-button">' . esc_html( $view_button_label ) . '</a>';
	}

	return $output;
}

/**
 * Get activity button label.
 *
 * @param string $activity_type Activity type.
 * @return string
 */
function olab_get_activity_button_label( $activity_type ) {
	switch ( $activity_type ) {
		case 'edited_group_document' :
		case 'added_group_document' :
			return __( 'View File', 'openlab-activity-block' );
		case 'bp_doc_created' :
		case 'bp_doc_edited' :
		case 'bp_doc_comment' :
			return __( 'View Doc', 'openlab-activity-block' );
		case 'bpeo_create_event' :
			return __( 'View Event', 'openlab-activity-block' );
		case 'created_announcement' :
			return __( 'View Announcement', 'openlab-activity-block' );
		case 'created_announcement_reply' :
		case 'bbp_reply_create' :
			return __( 'View Reply', 'openlab-activity-block' );
		case 'bbp_topic_create' :
			return __( 'View Discussion Topic', 'openlab-activity-block' );
		case 'new_blog_post' :
			return __( 'View Post', 'openlab-activity-block' );
		case 'new_blog_comment' :
			return __( 'View Comment', 'openlab-activity-block' );
		case 'created_group' :
		case 'joined_group' :
		case 'bpges_notice' :
			return __( 'View Group', 'openlab-activity-block' );
		case 'new_blog' :
			return __( 'View Site', 'openlab-activity-block' );
		case 'new_avatar' :
		case 'updated_profile' :
			return __( 'View Profile', 'openlab-activity-block' );
		default:
			return __( 'View', 'openlab-activity-block' );
	}
}

/**
 * Get BP activity button url.
 *
 * @param object $activity Activity object.
 * @return string
 */
function olab_get_activity_button_url( $activity ) {
	switch ( $activity->type ) { // @phpstan-ignore-line
		case 'edited_group_document':
		case 'added_group_document':
			$document = new BP_Group_Documents( (string) $activity->secondary_item_id ); // @phpstan-ignore-line
			return $document->get_url( false ); // @phpstan-ignore-line
		case 'bp_doc_created':
		case 'bp_doc_edited':
			return $activity->primary_link; // @phpstan-ignore-line
		case 'created_group':
		case 'joined_group':
		case 'bpges_notice':
			$group = bp_get_group_by( 'id', $activity->item_id ); // @phpstan-ignore-line
			return bp_get_group_permalink( $group );
		case 'default':
			return $activity->primary_link; // @phpstan-ignore-line
	}

	return $activity->primary_link; // @phpstan-ignore-line
}

/**
 * Get group id by site id.
 *
 * @param int $blog_id ID of the blog.
 * @return int
 */
function olab_get_group_id_by_blog_id( $blog_id ) {
	global $wpdb, $bp;

	if ( ! bp_is_active( 'groups' ) ) {
		return 0;
	}

	$group_id = wp_cache_get( $blog_id, 'site_group_ids' );
	if ( false === $group_id ) {
		// phpcs:ignore WordPress.DB
		$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'wds_bp_group_site_id' AND meta_value = %d", $blog_id ) );
		if ( null === $group_id ) {
			$group_id = 0;
		}
		wp_cache_set( $blog_id, $group_id, 'site_group_ids' );
	}

	return (int) $group_id;
}

/**
 * Get group type by site id.
 *
 * @param int $blog_id ID of the blog.
 * @return string
 */
function olab_get_group_type_by_blog_id( $blog_id ) {
	// Get group id.
	$group_id = olab_get_group_id_by_blog_id( $blog_id );

	// Get group type.
	$group_type = groups_get_groupmeta( $group_id, 'wds_group_type' );

	return $group_type;
}

/**
 * Determine whether to show the 'Activity Source' setting on a given site.
 *
 * @param int $site_id ID of the site.
 * @return bool
 */
function openlab_activity_block_show_activity_source_for_site( $site_id ) {
	// Never show if the Connections plugin is not available.
	if ( ! defined( 'OPENLAB_CONNECTIONS_PLUGIN_URL' ) ) {
		return false;
	}

	$group_id = olab_get_group_id_by_blog_id( $site_id );
	if ( ! $group_id ) {
		return false;
	}

	$connections = \OpenLab\Connections\Connection::get( [ 'group_id' => $group_id ] ); // @phpstan-ignore-line

	return ! empty( $connections );
}
