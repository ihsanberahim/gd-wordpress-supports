<?php
/**
 * Created by PhpStorm.
 * User: ihsanberahim
 * Date: 21/07/2019
 * Time: 5:19 PM
 */

namespace GoalDriven\Supports\Services;


class VirtualPage {
	public function __construct( $slug, $post_title = '', $post_content = '', $overrides = [] ) {
		//just needs to be a number, negatives are fine
		$this->ID          = rand( 0, 9999 ) * - 1;
		$this->post_author = rand( 0, 9999 ) * - 1;

		$this->guid = home_url("/{$slug}");

		$this->post_title = $post_title;

		//put your custom content here
		$this->post_content = $post_content;

		$this->post_name      = 'virtual_page';
		$this->post_type      = 'page';
		$this->post_status    = 'published';
		$this->comment_status = 'closed';
		$this->ping_status    = 'closed';
		$this->comment_count  = 0;

		//dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
		$this->post_date     = current_time( 'mysql' );
		$this->post_date_gmt = current_time( 'mysql', 1 );

		collect( array_keys( $overrides ) )->each( function ( $key ) use ( $overrides ) {
			$this->{$key} = $overrides[ $key ];
		} );
	}
}