<?php
/**
 * wp eval-file test.php --path=$WP_FRESH
 */

use GoalDriven\Supports\Services\Component;

require_once( 'vendor/autoload.php' );

echo "\n\nTEST: Component::requirecss()...\n";

echo ( new Component( __DIR__ ) )->requirecss( 'transfer-stock-style', '/transfer-stock.css' );

echo "\n\nTEST: Component::requirejs()...\n";

echo ( new Component( __DIR__ ) )->requirejs( 'transfer-stock-lib', '/transfer-stock-lib.js',
	( new Component( __DIR__ ) )->requirejs( false, '/transfer-stock.js' )
);

echo "\n\nTEST: gd_request()...\n";

var_dump( gd_request()->all() );

echo "\n\nTEST: ValidatorFactory...\n";

$validator = gd_validator()->make(
	$data = [
		'input1' => '1'
	],
	$rules = [
		'input2' => 'required'
	]
);

var_dump( $validator->fails() );
var_dump( $validator->passes() );
var_dump( $validator->errors() );

echo "\n\nTEST: Mysql...\n";

$post = gd_mysql()->table('posts')->first();

var_dump( $post );

echo "\n\nTEST: Log...\n";

gd_log()->info('something');
gd_log('custom')->info('something');

echo "\n\nTEST: Carbon...\n";

echo gd_now_formatted() . "\n";

var_dump(
	gd_carbon_validate(
		gd_now_formatted()
	)
);

var_dump(
	gd_carbon_validate(
		null
	)
);
