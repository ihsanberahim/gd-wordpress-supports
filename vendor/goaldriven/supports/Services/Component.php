<?php

namespace GoalDriven\Supports\Services;

use MatthiasMullie\Minify;

class Component {
	public $publicDir = __FILE__;

	public function __construct( $pluginFilePath = null ) {
		$this->publicDir = dirname( $pluginFilePath ? $pluginFilePath : $this->publicDir );
	}

	function wp_require_object( $func_name, $object ) {
		ob_start();
		?>
        let <?= $func_name ?> = () => {
        return <?= json_encode( $object ) ?>;
        }
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $this->minifyJs( $content );
	}

	function wp_cache_buster( $url ) {
		$hash = defined( 'WP_DEBUG' ) && WP_DEBUG ? md5( rand() ) : '';

		return $url . '?' . $hash;
	}

	function wp_get_resource_uri() {
		if ( str_contains( $this->publicDir, 'themes' ) ) {
			return get_stylesheet_directory_uri();
		} else if ( str_contains( $this->publicDir, 'plugins' ) ) {
			return content_url( '/plugins/' ) . gd_get_plugin_dirname( $this->publicDir );
		} else {
			return home_url( '/' );
		}
	}

	function requirejs( $module, $uri, $onload = '' ) {
		ob_start();
		?>
        (function (dom, resource_uri) {
            dom.setAttribute('src', `${resource_uri}<?= $this->wp_cache_buster( $uri ); ?>`);

		<?php if ( ! empty( $onload ) ): ?>
            dom.onload = () => {
			<?= $onload; ?>
            }
		<?php endif; ?>

		<?php if ( $module ): ?>
            if(!window['<?= implode( "']['", explode( '.', $module ) ); ?>']) {
                document.head.appendChild(dom);
            } else if(typeof dom.onload === 'function') {
                dom.onload();
            }
		<?php else: ?> document.head.appendChild(dom);
		<?php endif; ?>
        }(
            document.createElement('script'),
            '<?= $this->wp_get_resource_uri(); ?>')
            );
		<?php
		$content = ob_get_contents();

		ob_end_clean();

		return $this->minifyJs( $content );
	}

	function requirecss( $module, $uri, $onload = '' ) {
		ob_start();
		?>
        (function (dom, dom_id, resource_uri) {
            dom.setAttribute('rel', 'stylesheet');
            dom.setAttribute('href', `${resource_uri}<?= $this->wp_cache_buster( $uri ); ?>`);
            dom.setAttribute('id', dom_id);

		<?php if ( ! empty( $onload ) ): ?>
            dom.onload = () => {
			    <?= $onload; ?>
            }
		<?php endif; ?>

		<?php if ( $module ): ?>
            if(!document.getElementById(dom_id)) {
                document.head.appendChild(dom);
            }else if(typeof dom.onload === 'function'){
                dom.onload();
            }
		<?php else: ?> document.head.appendChild(dom);
		<?php endif; ?>
        }(
            document.createElement('link'),
            "requirecss-<?= $module ?>",
            '<?= $this->wp_get_resource_uri(); ?>')
            );
		<?php
		$content = ob_get_contents();

		ob_end_clean();

		return $this->minifyJs( $content );
	}

	protected function minifyJs( $content ) {
		$minifier = new Minify\JS( $content );

	    return $minifier->minify() . ';';
    }
}