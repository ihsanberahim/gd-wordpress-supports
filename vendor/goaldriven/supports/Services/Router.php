<?php
/**
 * Created by PhpStorm.
 * User: ihsanberahim
 * Date: 21/07/2019
 * Time: 4:24 PM
 */

namespace GoalDriven\Supports\Services;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;


class Router {
	public static function loadRouteFromFile( $file ) {
		require_once( $file );
	}

	/**
	 * @return RouteCollector
	 */
	private static function collector() {
		global $gd_router_collector;

		if ( ! $gd_router_collector ) {
			$gd_router_collector = new RouteCollector(
				new \FastRoute\RouteParser\Std(), new \FastRoute\DataGenerator\GroupCountBased()
			);
		}

		return $gd_router_collector;
	}

	/**
	 * @return \FastRoute\Dispatcher\GroupCountBased;
	 */
	private static function dispatcher() {
		/*
				$options = [
					'cacheFile'     => WP_CONTENT_DIR . "/gd-route.cache", // required
					'cacheDisabled' => WP_DEBUG, // optional
				];
		*/

		$routeData = self::collector()->getData();

		return new \FastRoute\Dispatcher\GroupCountBased( $routeData );
	}

	public static function register() {
		return self::collector();
	}

	public static function httpMeta() {
		// Fetch method and URI from somewhere
		$method = $_SERVER['REQUEST_METHOD'];
		$uri    = $_SERVER['REQUEST_URI'];

		// Strip query string (?foo=bar) and decode URI
		if ( false !== $pos = strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, $pos );
		}

		$uri = rawurldecode( $uri );

		$uri = rtrim( $uri, '/' );

		$segments = explode( '/', $uri );
		$slug     = $segments[ count( $segments ) - 1 ];

		return collect( [
			'method' => $method,
			'uri'    => $uri,
			'slug'   => $slug,
		] );
	}

	/**
	 * @param $uri
	 *
	 * @return mixed
	 */
	public static function match() {
		$dispatcher = self::dispatcher();
		$http_meta  = self::httpMeta();

		$route_info = $dispatcher->dispatch( $http_meta->get( 'method' ), $http_meta->get( 'uri' ) );

		$result = $http_meta->toArray();

		switch ( $route_info[0] ) {
			case Dispatcher::NOT_FOUND:
				return false;
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				$result += [
					'allowedMethod' => $route_info[1],
					'dispatcher'    => $dispatcher
				];
				break;
			case Dispatcher::FOUND:
				$result += [
					'handler'    => $route_info[1],
					'vars'       => $route_info[2],
					'dispatcher' => $dispatcher
				];
				break;
		}

		return collect( $result );
	}

	public static function run() {
		$route_meta = self::match();

		if ( $route_meta !== false ) {
			$vars    = $route_meta->get( 'vars', [] );
			$handler = $route_meta->get( 'handler', function () {
				// noop
			} );
			$slug    = $route_meta->get( 'slug', '' );

			// Build request
			$request        = gd_request();
			$request->route = collect( $vars );
			$request->user  = wp_get_current_user();

			try {
				// Logic either to call
				// * tuple static context
				// * tuple object context
				// * function
				if ( is_array( $handler ) ) {
					$class_name  = $handler[0];
					$method_name = $handler[1];

					if ( ! is_a( $method_name, $class_name ) ) {
						$instance = new $class_name;

						$post_content = $instance->{$method_name}( $request );
					} else {
						$post_content = $handler( $request );
					}
				} else {
					$post_content = $handler( $request );
				}
			} catch ( Exception $e ) {
				$error_code = $e->getCode();

				if ( $error_code == 0 ) {
					$error_code = 500;
				}

				wp_die(
					new \WP_Error( $error_code, $e->getMessage() )
				);
			}

			// Controller return false to ignore
			if ( $post_content === false ) {
				return;
			}

			// Response json if the controller method result are array or object
			if ( get_class( $post_content ) === 'stdClass' || is_array( $post_content ) ) {
				header( 'Content-Type: application/json', true, 200 );
				echo json_encode( (array) $post_content );
				exit;
			}

			// Hook wordpress page content
			add_filter( 'the_posts', function ( $posts ) use ( $slug, $post_content, $request ) {

				// Force page and singular
				global $wp_query;

				$wp_query->is_page     = true;
				$wp_query->is_singular = true;
				$wp_query->is_home     = false;
				$wp_query->is_archive  = false;
				$wp_query->is_category = false;
				unset( $wp_query->query['error'] );
				$wp_query->query_vars['error'] = '';
				$wp_query->is_404              = false;


				// Build virtual page
				$overrides  = $request->get( 'overrides', [] );
				$post_title = $request->get( 'post_title', '' );

				$virtual_page = new VirtualPage( $slug, $post_title, $post_content, $overrides );

				$posts = [ $virtual_page ];

				return $posts;
			} );
		}

	}

	public static function cleanUri( $uri ) {
		$uri = rtrim( $uri, '/' );
		$uri = ltrim( $uri, '/' );

		return $uri;
	}
}