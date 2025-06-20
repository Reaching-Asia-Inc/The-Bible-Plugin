<?php

namespace Tests\Services;

use CodeZone\Bible\Services\BaseRequest;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound
class ConcreteBaseRequest extends BaseRequest
{
    private array $url_params = [];
    private string $method_value = 'GET';
    private array $get_params = [];
    private array $post_params = [];

    public function method(): string
    {
        return $this->method_value;
    }

    public function all_url_params(): array
    {
        return $this->url_params;
    }

    public function all_post(): array
    {
        return $this->post_params;
    }

    public function all_get(): array
    {
        return $this->get_params;
    }

    // Helper methods for testing
    public function set_method( string $method ): void
    {
        $this->method_value = $method;
    }

    public function set_url_params( array $params ): void
    {
        $this->url_params = $params;
    }

    public function set_get_params( array $params ): void
    {
        $this->get_params = $params;
    }

    public function set_post_params( array $params ): void
    {
        $this->post_params = $params;
    }
}

class BaseRequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_all_parameters_based_on_request_method()
    {
        $request = new ConcreteBaseRequest();

        // Test with GET method
        $request->set_method( 'GET' );
        $request->set_get_params( [ 'name' => 'John', 'age' => 30 ] );
        $request->set_url_params( [ 'id' => 123 ] );

        $all_params = $request->all();
        $this->assertEquals( [ 'name' => 'John', 'age' => 30, 'id' => 123 ], $all_params );

        // Test with POST method
        $request->set_method( 'POST' );
        $request->set_post_params( [ 'email' => 'john@example.com', 'password' => 'secret' ] );

        $all_params = $request->all();
        $this->assertEquals( [ 'email' => 'john@example.com', 'password' => 'secret', 'id' => 123 ], $all_params );
    }

    /**
     * @test
     */
    public function it_gets_parameter_value_checking_request_method()
    {
        $request = new ConcreteBaseRequest();

        // Test with GET method
        $request->set_method( 'GET' );
        $request->set_get_params( [ 'name' => 'John', 'age' => 30 ] );
        $request->set_url_params( [ 'id' => 123 ] );

        $this->assertEquals( 'John', $request->get( 'name' ) );
        $this->assertEquals( 30, $request->get( 'age' ) );
        $this->assertEquals( 123, $request->get( 'id' ) );
        $this->assertEquals( 'default', $request->get( 'unknown', 'default' ) );

        // Test with POST method
        $request->set_method( 'POST' );
        $request->set_post_params( [ 'email' => 'john@example.com', 'password' => 'secret' ] );

        $this->assertEquals( 'john@example.com', $request->get( 'email' ) );
        $this->assertEquals( 'secret', $request->get( 'password' ) );
        $this->assertEquals( 123, $request->get( 'id' ) ); // URL params take precedence
    }

    /**
     * @test
     */
    public function it_casts_string_values_to_boolean()
    {
        $request = new ConcreteBaseRequest();

        $request->set_method( 'GET' );
        $request->set_get_params([
            'true_string' => 'true',
            'false_string' => 'false',
            'other_string' => 'hello'
        ]);

        $this->assertTrue( $request->get( 'true_string' ) );
        $this->assertFalse( $request->get( 'false_string' ) );
        $this->assertEquals( 'hello', $request->get( 'other_string' ) );
    }

    /**
     * @test
     */
    public function it_checks_if_key_exists_and_is_not_null()
    {
        $request = new ConcreteBaseRequest();

        $request->set_method( 'GET' );
        $request->set_get_params([
            'name' => 'John',
            'age' => 30,
            'null_value' => null
        ]);

        $this->assertTrue( $request->has( 'name' ) );
        $this->assertTrue( $request->has( 'age' ) );
        $this->assertFalse( $request->has( 'null_value' ) );
        $this->assertFalse( $request->has( 'unknown' ) );
    }

    /**
     * @test
     */
    public function it_checks_if_key_exists_and_is_string()
    {
        $request = new ConcreteBaseRequest();

        $request->set_method( 'GET' );
        $request->set_get_params([
            'name' => 'John',
            'age' => 30,
            'null_value' => null
        ]);

        $this->assertTrue( $request->is_string( 'name' ) );
        $this->assertFalse( $request->is_string( 'age' ) );
        $this->assertFalse( $request->is_string( 'null_value' ) );
        $this->assertFalse( $request->is_string( 'unknown' ) );
    }

    /**
     * @test
     */
    public function it_supports_magic_property_access()
    {
        $request = new ConcreteBaseRequest();

        $request->set_method( 'GET' );
        $request->set_get_params( [ 'name' => 'John', 'age' => 30 ] );

        $this->assertEquals( 'John', $request->name );
        $this->assertEquals( 30, $request->age );
        $this->assertNull( $request->unknown );
    }
}
