<?php

/**
 * Modify the output of the activity items
 */
function olab_get_activity_action( $activity = null, $displayStyle = 'full' ) {
	global $activities_template;

	if ( null === $activity ) {
		$activity = $activities_template->activity;
	}

	// Get activity body content
	$output = $activity->action;
	
	// Remove link from the user's display name
	if( $displayStyle == 'simple' ) {
		$activity_user_link = bp_core_get_userlink( $activity->user_id );
		$output = str_replace( $activity_user_link, $activity->display_name, $output );
	}

	// Remove "in the group/forum" text from the activity on the group activity stream
	if( bp_is_group() ) {
		$group = bp_get_group( bp_get_current_group_id() );
		$group_link = bp_get_group_permalink( $group );
		$output = preg_replace( '/in the group <a href="[^"]+">' . preg_quote( bp_get_group_name() ) . '<\/a>/', '', $output );
		$output = str_replace( 'in the forum <a href="' . $group_link . 'forum/">' . bp_get_group_name() . '</a>', '', $output );
		$output = str_replace( 'in <a href="' . $group_link . '">' . bp_get_group_name() . '</a>', '', $output );
	} else {
		if( $activity->type == 'bbp_topic_create' || $activity->type == 'bbp_reply_create' ) {
			$output = str_replace( 'in the forum', 'in the group', $output );
		}
	}

	if( $activity->type == 'added_group_document' ) {
		$output = str_replace( 'uploaded the file', 'added the file', $output );
	}

	// Create DateTime from the activity date
	$activity_datetime = new DateTime( $activity->date_recorded );

	// Create TimeZone from the timezone selected in the WP Settings
	$wp_timezone = new DateTimeZone( wp_timezone_string() );

	// Set timezone to the activity DateTime
	$activity_datetime->setTimezone( $wp_timezone );

	// Modify activity date format, remove link and add "on" before the date
	$output .= ' on ' . $activity_datetime->format('F d, Y \a\t g:i a');
	$output = wpautop( $output );

	// Activity view button
	$view_button_label = olab_get_activity_button_label( $activity->type );
	$view_button_link = olab_get_activity_button_url( $activity );

	// Append activity view button
	if( $view_button_label ) {
		$output .= '<a href="' . $view_button_link . '" class="olab-activity-item-button">' . $view_button_label . '</a>';
	}

	return $output;
}


/**
 * Get activity button label
 */
function olab_get_activity_button_label( $activity_type ) {
    switch ( $activity_type ) {
		case 'edited_group_document' :
		case 'added_group_document' :
			return 'View File';
		case 'bp_doc_created' :
		case 'bp_doc_edited' :
		case 'bp_doc_comment' :
			return 'View Doc';
		case 'bpeo_create_event' :
			return 'View Event';
		case 'created_announcement' :
			return 'View Announcement';
		case 'created_announcement_reply' :
		case 'bbp_reply_create' :
			return 'View Reply';
		case 'bbp_topic_create' :
			return 'View Discussion Topic';
		case 'new_blog_post' :
			return 'View Post';
		case 'new_blog_comment' :
			return 'View Comment';
		case 'created_group' :
		case 'joined_group'	:
		case 'bpges_notice' :
			return 'View Group';
		case 'new_blog' :
			return 'View Site';
		case 'new_avatar' :
		case 'updated_profile' :
			return 'View Profile';
        default:
            return 'View';
	}
}

/**
 * Get BP activity button url
 */
function olab_get_activity_button_url( $activity ) {
    global $activities_template;

	if( null === $activity ) {
		$activity = $activities_template->activity;
	}

	switch( $activity->type ) {
		case 'edited_group_document':
		case 'added_group_document':
			$document = new BP_Group_Documents( (string)$activity->secondary_item_id );
			return $document->get_url( false );
		case 'bp_doc_created':
		case 'bp_doc_edited':
			return $activity->primary_link;
		case 'created_group':
		case 'joined_group':
		case 'bpges_notice':
			$group = bp_get_group_by( 'id', $activity->item_id );
			return bp_get_group_permalink( $group );
		case 'default':
			return $activity->primary_link;
	}

	return $activity->primary_link;
}

/**
 * Get group id by site id
 */
function olab_get_group_id_by_blog_id( $blog_id ) {
	global $wpdb, $bp;

	if ( ! bp_is_active( 'groups' ) ) {
		return 0;
	}

	$group_id = wp_cache_get( $blog_id, 'site_group_ids' );
	if ( false === $group_id ) {
		$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'wds_bp_group_site_id' AND meta_value = %d", $blog_id ) ); // WPCS: unprepared SQL ok.
		if ( null === $group_id ) {
			$group_id = 0;
		}
		wp_cache_set( $blog_id, $group_id, 'site_group_ids' );
	}

	return (int) $group_id;
}

/**
 * Get group type by site id
 */
function olab_get_group_type_by_blog_id( $blog_id ) {
	// Get group id
	$group_id = olab_get_group_id_by_blog_id( $blog_id );

	// Get group type
	$group_type = groups_get_groupmeta( $group_id, 'wds_group_type' );

	return $group_type;
}