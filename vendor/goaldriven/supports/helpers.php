<?php

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\Support\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if ( ! function_exists( 'gd_setup_template_update_checker' ) ) {
	function gd_setup_template_update_checker( $theme_function_file_path, $branch = 'master' ) {
		$theme_slug   = get_template();
		$theme        = wp_get_theme();
		$theme_uri    = $theme->get( 'ThemeURI' );
		$const_prefix = str_replace( '-', '_', strtoupper( $theme_slug ) );

		// Handle error
		// a) when theme dev. put wrong 'Theme URI'
		// b) when fail to setup authentication
		try {
			$updateChecker = Puc_v4_Factory::buildUpdateChecker(
				"{$theme_uri}/raw/{$branch}/updates.json", // a)
				$theme_function_file_path,
				$theme_slug
			);

			// b)
			if ( $consumer_key = immutable( "{$const_prefix}_CONSUMER_KEY", null ) ) {
				if ( $consumer_secret = immutable( "{$const_prefix}_CONSUMER_SECRET", null ) ) {
					$credentials = [
						'consumer_key'    => $consumer_key,
						'consumer_secret' => $consumer_secret,
					];

					$updateChecker->setAuthentication( $credentials );
				}
			}

		} catch ( Exception $e ) {
			gd_log()->error( $e->getMessage() );

			return false;
		}

		return $updateChecker;
	}
}
if ( ! function_exists( 'gd_setup_plugin_update_checker' ) ) {
	function gd_setup_plugin_update_checker( $plugin_file_path ) {
		$plugin_slug  = gd_plugin_slug( $plugin_file_path );
		$plugin_data  = gd_plugin_data( $plugin_file_path );
		$plugin_uri   = $plugin_data->get( 'PluginURI' );
		$const_prefix = str_replace( '-', '_', strtoupper( $plugin_slug ) );

		// Handle error
		// a) when plugin dev. put wrong 'Plugin URI'
		// b) when fail to setup authentication
		try {
			$updateChecker = Puc_v4_Factory::buildUpdateChecker(
				$plugin_uri, // a)
				$plugin_file_path,
				$plugin_slug
			);

			// b)
			if ( $consumer_key = immutable( "{$const_prefix}_CONSUMER_KEY", null ) ) {
				if ( $consumer_secret = immutable( "{$const_prefix}_CONSUMER_SECRET", null ) ) {
					$credentials = [
						'consumer_key'    => $consumer_key,
						'consumer_secret' => $consumer_secret,
					];

					$updateChecker->setAuthentication( $credentials );
				}
			}

		} catch ( Exception $e ) {
			gd_log()->error( $e->getMessage() );

			return false;
		}

		return $updateChecker;
	}
}

if ( ! function_exists( 'gd_plugin_data' ) ) {
	function gd_plugin_data( $plugin_file_path ) {
		if( is_admin() ) {
			return collect( get_plugin_data( $plugin_file_path ) );
		}

		return collect([]);
	}
}

if ( ! function_exists( 'gd_plugin_slug' ) ) {
	function gd_plugin_slug( $plugin_file_path ) {
		return explode( '.', basename( $plugin_file_path ) )[0];
	}
}

if ( ! function_exists( 'gd_new_comp' ) ) {
	function gd_new_comp( $pluginFilePath = null ) {
		return new GoalDriven\Supports\Services\Component( $pluginFilePath );
	}
}

if ( ! function_exists( 'gd_get_plugin_dirname' ) ) {
	function gd_get_plugin_dirname( $path ) {
		$paths   = explode( 'plugins', $path );
		$slashes = explode( '/', $paths[1] );

		return $slashes[1];
	}
}

if ( ! function_exists( 'gd_get_plugin_dir' ) ) {
	function gd_get_plugin_dir( $path ) {
		$plugin_dirname = gd_get_plugin_dirname( $path );

		return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_dirname;
	}
}

if ( ! function_exists( 'gd_request' ) ) {
	/**
	 * @return \Illuminate\Http\Request;
	 */
	function gd_request() {
		global $gd_request;

		if ( ! $gd_request ) {
			$gd_request = \Illuminate\Http\Request::capture();
		}

		return $gd_request;
	}
}

if ( ! function_exists( 'gd_validator' ) ) {
	function gd_validator() {
		return new \GoalDriven\Supports\ValidatorFactory();
	}
}

if ( ! function_exists( 'gd_mysql' ) ) {
	function gd_mysql() {
		return new \GoalDriven\Supports\Drivers\Mysql();
	}
}

if ( ! function_exists( 'gd_log' ) ) {
	/**
	 * @param string $name
	 * @param int $level
	 *
	 * @return Logger
	 */
	function gd_log( $name = 'gd', $level = 200 ) {

		$log = new Logger( str_slug( $name ) );

		try {
			$log->pushHandler( new StreamHandler( WP_CONTENT_DIR . "/{$name}.log", $level ) );
		} catch ( Exception $e ) {
		}

		return $log;
	}
}

if ( ! function_exists( 'gd_now' ) ) {
	function gd_now() {
		return Carbon::parse( current_time( 'mysql' ) );
	}
}

if ( ! function_exists( 'gd_now_formatted' ) ) {
	function gd_now_formatted() {
		return gd_now()->format( 'Y-m-d H:i:s' );
	}
}

if ( ! function_exists( 'gd_carbon_validate' ) ) {
	function gd_carbon_validate( $time_string ) {
		return $time_string && Carbon::createFromFormat( 'Y-m-d H:i:s', $time_string ) !== false;
	}
}

if ( ! function_exists( 'immutable' ) ) {
	function immutable( $key, $default = null ) {
		return defined( $key ) ? constant( $key ) : $default;
	}
}

if ( ! function_exists( 'gd_view_factory' ) ) {
	function gd_view_factory( $view, $cache ) {
		// Configuration
		// Note that you can set several directories where your templates are located
		$pathsToTemplates        = [ $view ];
		$pathToCompiledTemplates = $cache;
		// Dependencies
		$filesystem      = new Filesystem;
		$eventDispatcher = new Dispatcher( new Container );
		// Create View Factory capable of rendering PHP and Blade templates
		$viewResolver  = new EngineResolver;
		$bladeCompiler = new BladeCompiler( $filesystem, $pathToCompiledTemplates );
		$viewResolver->register( 'blade', function () use ( $bladeCompiler ) {
			return new CompilerEngine( $bladeCompiler );
		} );
		$viewResolver->register( 'php', function () {
			return new PhpEngine;
		} );
		$viewFinder  = new FileViewFinder( $filesystem, $pathsToTemplates );
		$viewFactory = new Factory( $viewResolver, $viewFinder, $eventDispatcher );

		return $viewFactory;
	}
}

if ( ! function_exists( 'gd_config' ) ) {
	function gd_config( $json ) {
		if ( is_file( $json ) ) {
			$json = require $json;
		}

		return collect( $json );
	}
}