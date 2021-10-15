<?php
/*
Plugin Name: REST API Search by ID
Plugin URI: https://github.com/humanmade/rest-api-search-by-id
Description: Support the "include" and "exclude" parameters on the /search REST API endpoint.
Version: 1.0.0
Author: Human Made
Author URI: https://humanmade.com/
License: GPL2
*/

namespace REST_API_Search_By_ID;

use WP_REST_Request;

/**
 * Ensure values are a proper array and convert all values to integers.
 *
 * @param array|string $id_list List of IDs in a variety of possible formats.
 * @return int[] Array of IDs.
 */
function prepare_id_list( $id_list ) : array {
	return array_map( 'intval', rest_sanitize_array( $id_list ) );
}

/**
 * Filter a REST API search endpoint request to support include/exclude.
 *
 * @param array           $query_args Key value array of query var to query value.
 * @param WP_REST_Request $request    Incoming API request.
 * @return array Filtered array of query vars.
 */
function handle_include_exclude( array $query_args, WP_REST_Request $request ) : array {
	if ( isset( $request['include'] ) ) {
		$query_args['post__in'] = prepare_id_list( $request['include'] );
	}

	if ( isset( $request['exclude'] ) ) {
		$query_args['post__not_in'] = prepare_id_list( $request['exclude'] );
	}

	return $query_args;
}
add_filter( 'rest_post_search_query', __NAMESPACE__ . '\\handle_include_exclude', 10, 2 );

/**
 * Enqueue frontend bundle providing a useful hook.
 */
function enqueue_hook_js() : void {
	wp_enqueue_script(
		'use-resource-by-id',
		plugins_url( 'js/use-resource-by-id.min.js', __FILE__ ),
		[
			'wp-api-fetch',
			'wp-data',
			'react',
		],
		'1.0.0'
	);
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_hook_js' );
