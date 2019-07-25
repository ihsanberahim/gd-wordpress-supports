<?php
/**
 * Created by PhpStorm.
 * User: ihsanberahim
 * Date: 17/05/2019
 * Time: 6:47 AM
 */

namespace GoalDriven\Supports;

use Illuminate\Validation;
use Illuminate\Translation;
use Illuminate\Filesystem\Filesystem;

/**
 * Class ValidatorFactory
 * @package GoalDriven\Supports
 *
 * @method \Illuminate\Validation\Validator make(array $data, array $rules, array $messages = [], array $customAttributes = [])
 */
class ValidatorFactory {
	private $factory;

	public function __construct() {
		$this->factory = new Validation\Factory(
			$this->loadTranslator()
		);
	}

	protected function loadTranslator() {
		$filesystem = new Filesystem();
		$loader     = new Translation\FileLoader(
			$filesystem, dirname( dirname( __FILE__ ) ) . '/lang' );
		$loader->addNamespace(
			'lang',
			dirname( dirname( __FILE__ ) ) . '/lang'
		);
		$loader->load( 'en', 'validation', 'lang' );

		return new Translation\Translator( $loader, 'en' );
	}

	public function __call( $method, $args ) {
		return call_user_func_array(
			[ $this->factory, $method ],
			$args
		);
	}
}