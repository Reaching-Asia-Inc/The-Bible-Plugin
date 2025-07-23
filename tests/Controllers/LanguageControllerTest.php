<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Requests\Request;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsLanguages;
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
        $this->assertEquals( $response['id'], $language_id );
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
        $this->assertIsArray( $response );
        $this->assertGreaterThan( 0, count( $response ) );
        foreach ( $response as $option ) {
            $this->assertIsString( $option['value'] );
            $this->assertIsString( $option['itemText'] );
        }
    }

    /**
     * @test
     */
    public function it_returns_all_languages()
    {
        // Create test data
        $fixture = $this->fixture( 'languages' );

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

        foreach ( $fixture['data'] as $expected ) {
            $matches = array_values(array_filter($response, function ( $item ) use ( $expected ) {
                return $item['id'] == $expected['id'];
            }));
            if ( !count( $matches ) ) {
                $this->fail( 'Could not find language ' . $expected['id'] );
            }
            $this->assertEquals( $matches[0]['code'], $expected['iso'] );
        }
    }

    /**
     * @test
     */
    public function it_searches_languages_by_term()
    {
        // Create test data
        $search_term = 'Spanish';
        $fixture = $this->fixture( 'languages/search/' . strtolower( $search_term ) );
;
        $this->mock_api_response( 'languages/search/' . $search_term, new Response( 200, [], json_encode( $fixture ) ) );

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


        foreach ( $fixture['data'] as $expected ) {
            $matches = array_values(array_filter($response, function ( $item ) use ( $expected ) {
                return $item['id'] == $expected['id'];
            }));
            if ( !count( $matches ) ) {
                $this->fail( 'Could not find language ' . $expected['id'] );
            }
            $this->assertEquals( $matches[0]['code'], $expected['iso'] );
        }
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
        $this->assertEquals( '6414', $result['id'] );
        $this->assertEquals( 'English', $result['name'] );
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
        foreach ( $result as $language ) {
            $this->assertArrayHasKey( 'value', $language );
            $this->assertArrayHasKey( 'itemText', $language );
        }
    }

    /**
     * Test that the ApiServices settings page loads.
     * @test
     */
    public function it_can_search() {
        $languages = container()->get( BibleBrainsLanguages::class );
        $result    = $languages->search( 'Spanish' );
        $this->assertGreaterThan( 0, count( $result ) );
    }
}
