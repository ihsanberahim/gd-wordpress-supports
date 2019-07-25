<?php
/**
 * Created by PhpStorm.
 * User: ihsanberahim
 * Date: 18/05/2019
 * Time: 8:14 AM
 */

namespace GoalDriven\Supports\Drivers;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Container\Container;


/**
 * Class Mysql
 * @package GoalDriven\Supports\Drivers
 */
class Mysql extends Manager{
	/**
	 * The current globally used instance.
	 *
	 * @var Manager
	 */
	protected static $instance;

	public function __construct(Container $container = null) {
		parent::__construct($container);

		global $wpdb;

		$this->addConnection([
			'driver' => 'mysql',
			'host' => DB_HOST,
			'database' => DB_NAME,
			'username' => DB_USER,
			'password' => DB_PASSWORD,
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => $wpdb->prefix
		]);

		$this->setAsGlobal();
	}

	/**
	 * Enable the query log on the connection.
	 *
	 * @return void
	 */
	public function generalLogOn()
	{
		static::$instance->getDatabaseManager()->statement("SET GLOBAL general_log = 'ON'");
	}

	/**
	 * Disable the query log on the connection.
	 *
	 * @return void
	 */
	public function generalLogOff()
	{
		static::$instance->getDatabaseManager()->statement("SET GLOBAL general_log = 'OFF'");
	}
}