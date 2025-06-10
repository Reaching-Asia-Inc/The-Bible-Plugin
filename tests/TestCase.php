<?php

namespace Tests;

use Faker;
use WP_UnitTestCase;

abstract class TestCase extends WP_UnitTestCase {
	/**
	 * Set up the test case by starting a transaction and calling the parent's setUp method.
	 *
	 * This method is called before each test method.
	 * It starts a transaction using the global $wpdb object and then calls the parent's setUp method.
	 *
	 * @return void
	 */
	protected Faker\Generator $faker;

	public function __construct( ?string $name = null, array $data = [], $data_nme = '' ) {
		$this->faker = Faker\Factory::create();
		parent::__construct( $name, $data, $data_nme );
	}

	public function setUp(): void {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		parent::setUp();
	}

	/**
	 * The tearDown method is used to clean up any resources or connections after each test case is executed.
	 * In this specific case, it performs a rollback in the database using the global $wpdb variable of WordPress.
	 * It then calls the tearDown method of the parent class to ensure any additional cleanup tasks are performed.
	 * @return void
	 */
	public function tearDown(): void {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
		parent::tearDown();
	}

	/**
	 * Logs in as a new user and returns the user object.
	 *
	 * This method creates a new user using the given username, password, and email using the `wp_create_user` function.
	 * It then logs in as the newly created user using the `acting_as` method.
	 * Finally, it returns the user object of the newly created user.
	 *
	 * @return WP_User The user object of the newly created user.
	 */
	public function as_user( $username = null, $password = null, $email = null ) {
		$user = wp_create_user( $username ?? $this->faker->userName, $password ?? $this->faker->password, $email ?? $this->faker->email );
		$this->acting_as( $user );

		return $user;
	}

	/**
	 * Sets the current user and authenticates the user session as the specified user.
	 *
	 * @param int $user_id The ID of the user to act as.
	 *
	 * @return void
	 */
	public function acting_as( $user_id ) {
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
	}
}
