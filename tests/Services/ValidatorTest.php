<?php

namespace Tests\Services;

use CodeZone\Bible\Services\RequestInterface;
use CodeZone\Bible\Services\Validator;
use Tests\TestCase;

/**
 * @group services
 * @group validation
 */
class ValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_required_fields()
    {
        $validator = new Validator();

        // Test with missing required field
        $data = [];
        $rules = [ 'name' => 'required' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'name', $result );

        // Test with provided required field
        $data = [ 'name' => 'John' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_string_fields()
    {
        $validator = new Validator();

        // Test with non-string value
        $data = [ 'name' => 123 ];
        $rules = [ 'name' => 'string' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'name', $result );

        // Test with string value
        $data = [ 'name' => 'John' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_numeric_fields()
    {
        $validator = new Validator();

        // Test with non-numeric value
        $data = [ 'age' => 'abc' ];
        $rules = [ 'age' => 'numeric' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'age', $result );

        // Test with numeric value
        $data = [ 'age' => 25 ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_email_fields()
    {
        $validator = new Validator();

        // Test with invalid email
        $data = [ 'email' => 'invalid-email' ];
        $rules = [ 'email' => 'email' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'email', $result );

        // Test with valid email
        $data = [ 'email' => 'test@example.com' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_url_fields()
    {
        $validator = new Validator();

        // Test with invalid URL
        $data = [ 'website' => 'invalid-url' ];
        $rules = [ 'website' => 'url' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'website', $result );

        // Test with valid URL
        $data = [ 'website' => 'https://example.com' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_min_length_for_strings()
    {
        $validator = new Validator();

        // Test with string shorter than min
        $data = [ 'password' => 'abc' ];
        $rules = [ 'password' => 'min:6' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'password', $result );

        // Test with string longer than min
        $data = [ 'password' => 'abcdefgh' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_max_length_for_strings()
    {
        $validator = new Validator();

        // Test with string longer than max
        $data = [ 'username' => 'abcdefghijklmnopqrstuvwxyz' ];
        $rules = [ 'username' => 'max:10' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'username', $result );

        // Test with string shorter than max
        $data = [ 'username' => 'abcdef' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_in_rule()
    {
        $validator = new Validator();

        // Test with value not in allowed list
        $data = [ 'status' => 'invalid' ];
        $rules = [ 'status' => 'in:pending,approved,rejected' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'status', $result );

        // Test with value in allowed list
        $data = [ 'status' => 'approved' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_boolean_fields()
    {
        $validator = new Validator();

        // Test with non-boolean value
        $data = [ 'active' => 'yes' ];
        $rules = [ 'active' => 'boolean' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'active', $result );

        // Test with boolean value
        $data = [ 'active' => true ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );

        // Test with string boolean values
        $data = [ 'active' => '1' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_array_fields()
    {
        $validator = new Validator();

        // Test with non-array value
        $data = [ 'items' => 'not an array' ];
        $rules = [ 'items' => 'array' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'items', $result );

        // Test with array value
        $data = [ 'items' => [ 'item1', 'item2' ] ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_multiple_rules_for_a_field()
    {
        $validator = new Validator();

        // Test with multiple failing rules
        $data = [ 'email' => '' ];
        $rules = [ 'email' => 'required|email' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'email', $result );

        // Test with first rule passing, second failing
        $data = [ 'email' => 'not-an-email' ];
        $result = $validator->validate( $data, $rules );

        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'email', $result );

        // Test with all rules passing
        $data = [ 'email' => 'test@example.com' ];
        $result = $validator->validate( $data, $rules );

        $this->assertTrue( $result );
    }

    /**
     * @test
     */
    public function it_validates_request_objects()
    {
        $validator = new Validator();

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return test values
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'name', null, 'John' ],
                [ 'email', null, 'test@example.com' ],
                [ 'age', null, 25 ]
            ]);

        // Test with valid data
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'age' => 'numeric'
        ];

        $result = $validator->validate( $request, $rules );

        $this->assertTrue( $result );
    }
}
