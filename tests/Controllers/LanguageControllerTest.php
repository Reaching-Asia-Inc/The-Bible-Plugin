<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use CodeZone\Bible\Services\Request;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group controllers
 * @group languages
 * @group settings
 */
class LanguageControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_language_by_id()
    {
        // Create test data
        $language_id = '6414';

        $expected = $this->fixture( 'languages/6414' );

        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'id' => $language_id
            ]);

        // Create the controller
        $controller = new LanguageController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected, $response );
    }

    /**
     * @test
     */
    public function it_returns_languages_as_options()
    {
        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->getMock();

        $controller = new LanguageController();

        // Call the options method
        $response = $controller->options( $request );
        $this->assertArrayHasKey( 'data', $response );
        $this->assertIsArray( $response['data'] );
        $this->assertGreaterThan( 0, count( $response['data'] ) );
        foreach ( $response['data'] as $option ) {
            $this->assertIsString( $option['value'] );
            $this->assertIsString( $option['language_code'] );
            $this->assertIsString( $option['itemText'] );
        }
    }

    /**
     * @test
     */
    public function it_returns_all_languages()
    {
        // Create test data
        $expected_data = $this->fixture( 'languages' );

        // Create a mock Request object
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'search' => null,
                'paged' => 1,
                'limit' => 50
            ]);

        // Create the controller
        $controller = new LanguageController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function it_searches_languages_by_term()
    {
        // Create test data
        $search_term = 'Spanish';
        $expected_data = $this->fixture( 'languages/search/' . strtolower( $search_term ) );
;
        $this->mock_api_response( 'languages/search/' . $search_term, new Response( 200, [], json_encode( $expected_data ) ) );

        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'search' => $search_term
            ]);

        // Create the controller
        $controller = new LanguageController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function test_it_can_fetch_a_language()
    {
        // 1. Mock the request
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'id' => 6414
            ]);

        // 5. Run the controller
        $controller = new LanguageController();
        $result = $controller->show( $request );

        // 6. Assert the response
        $this->assertEquals( '6414', $result['data']['id'] );
        $this->assertEquals( 'English', $result['data']['name'] );
    }


    /**
     * Test that the controller can fetch language options.
     * @test
     */
    public function it_can_fetch_language_options() {
        // 1. Mock the request
        $request = $this->getMockBuilder( Request::class )
            ->onlyMethods( [ 'all_get' ] ) // or setMethods() for older PHPUnit
            ->getMock();

        // Configure the mock to return empty values
        $request->expects( $this->any() )
            ->method( 'all_get' )
            ->willReturn([
                'limit' => 2,
                'paged' => 1,
                'search' => null
            ]);

        // Create the controller
        $controller = new LanguageController();

        // Call the options method
        $result = $controller->options( $request );

        // Assert that the result contains the expected data
        $this->assertArrayHasKey( 'data', $result );
        foreach ( $result['data'] as $language ) {
            $this->assertArrayHasKey( 'value', $language );
            $this->assertArrayHasKey( 'itemText', $language );
        }
    }

    /**
     * Test that the BibleBrains settings page loads.
     * @test
     */
    public function it_can_search() {
        $languages = container()->get( Languages::class );
        $result    = $languages->search( 'Spanish' );
        $this->assertGreaterThan( 0, count( $result['data'] ) );
    }
}
