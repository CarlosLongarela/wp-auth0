<?php
/**
 * Contains Class TestLoginManager.
 *
 * @package WP-Auth0
 * @since 3.7.1
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestLoginManager.
 * Tests that WP_Auth0_LoginManager methods function as expected.
 */
class TestLoginManager extends TestCase {

	use setUpTestDb;

	/**
	 * Test that the default auth scopes are returned and filtered properly.
	 */
	public function testUserinfoScope() {
		$scope = WP_Auth0_LoginManager::get_userinfo_scope();
		$this->assertEquals( 'openid email profile', $scope );

		add_filter(
			'auth0_auth_scope', function( $default_scope, $context ) {
				$default_scope[] = $context;
				return $default_scope;
			}, 10, 2
		);

		$scope = WP_Auth0_LoginManager::get_userinfo_scope( 'auth0' );
		$this->assertEquals( 'openid email profile auth0', $scope );
	}

	/**
	 * Test that authorize URL params are built and filtered properly.
	 */
	public function testAuthorizeParams() {
		$test_client_id  = uniqid();
		$test_connection = uniqid();
		$auth_params     = WP_Auth0_LoginManager::get_authorize_params();

		$this->assertEquals( 'openid email profile', $auth_params['scope'] );
		$this->assertEquals( 'code', $auth_params['response_type'] );
		$this->assertEquals( site_url( 'index.php?auth0=1' ), $auth_params['redirect_uri'] );
		$this->assertNotEmpty( $auth_params['auth0Client'] );
		$this->assertNotEmpty( $auth_params['state'] );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( $test_connection );
		$this->assertEquals( $test_connection, $auth_params['connection'] );

		$options = WP_Auth0_Options::Instance();
		$options->set( 'client_id', $test_client_id );

		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( $test_client_id, $auth_params['client_id'] );

		$options->set( 'auth0_implicit_workflow', 1 );
		$auth_params = WP_Auth0_LoginManager::get_authorize_params();
		$this->assertEquals( add_query_arg( 'auth0', 1, wp_login_url() ), $auth_params['redirect_uri'] );
		$this->assertEquals( 'id_token', $auth_params['response_type'] );
		$this->assertNotEmpty( $auth_params['nonce'] );

		add_filter(
			'auth0_authorize_url_params', function( $params, $connection, $redirect_to ) {
				$params[ $connection ] = $redirect_to;
				return $params;
			}, 10, 3
		);

		$auth_params = WP_Auth0_LoginManager::get_authorize_params( 'auth0', 'https://auth0.com' );
		$this->assertEquals( 'https://auth0.com', $auth_params['auth0'] );
	}
}
