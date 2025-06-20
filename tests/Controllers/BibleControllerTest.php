<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\BibleController;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

class BibleControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_and_returns_error_if_validation_fails()
    {
        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return empty id
        $request->method( 'get' )
            ->willReturnMap([
                [ 'id', null, '' ]
            ]);

        // Create the controller
        $controller = new BibleController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response contains error information
        $this->assertArrayHasKey( 'code', $response );
        $this->assertArrayHasKey( 'message', $response );
        $this->assertArrayHasKey( 'errors', $response );
        $this->assertEquals( 400, $response['code'] );
    }

    /**
     * @test
     */
    public function it_returns_bible_data_for_valid_id()
    {
        // Create test data
        $bible_id = 'ENGESV';
        $expected_data = [
            'abbr' => 'ENGESV',
            'name' => 'English Standard Version',
            'language' => 'English'
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test bible_id
        $request->method( 'get' )
            ->willReturnMap([
                [ 'id', null, $bible_id ]
            ]);

        // Mock the Bibles service
        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'find' )
            ->with( $bible_id )
            ->willReturn( $expected_data );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function it_returns_bibles_as_options()
    {
        // Create test data
        $bible_data = [
            'data' => [
                [
                    'abbr' => 'ENGESV',
                    'name' => 'English Standard Version'
                ],
                [
                    'abbr' => 'ENGKJV',
                    'name' => 'King James Version'
                ]
            ]
        ];

        $expected_options = [
            'data' => [
                [
                    'value' => 'ENGESV',
                    'itemText' => 'English Standard Version'
                ],
                [
                    'value' => 'ENGKJV',
                    'itemText' => 'King James Version'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Mock the Bibles service
        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'all' )
            ->willReturn( $bible_data );
        $bibles->method( 'as_options' )
            ->with( $bible_data['data'] )
            ->willReturn( $expected_options['data'] );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleController();

        // Call the options method
        $response = $controller->options( $request );

        // Assert that the response contains the expected options
        $this->assertEquals( $expected_options, $response );
    }

    /**
     * @test
     */
    public function it_filters_bibles_by_language_code()
    {
        // Create test data
        $language_code = 'eng';
        $expected_data = [
            'data' => [
                [
                    'abbr' => 'ENGESV',
                    'name' => 'English Standard Version'
                ],
                [
                    'abbr' => 'ENGKJV',
                    'name' => 'King James Version'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test language_code
        $request->method( 'get' )
            ->willReturnMap([
                [ 'language_code', '', $language_code ],
                [ 'paged', 1, 1 ],
                [ 'limit', 50, 50 ],
                [ 'search', '', '' ]
            ]);

        // Mock the Bibles service
        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'for_languages' )
            ->with( [ $language_code ], [ 'limit' => 150 ] )
            ->willReturn( $expected_data );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function it_filters_bibles_by_search_term()
    {
        // Create test data
        $search_term = 'Standard';
        $all_bibles = [
            'data' => [
                [
                    'abbr' => 'ENGESV',
                    'name' => 'English Standard Version'
                ],
                [
                    'abbr' => 'ENGKJV',
                    'name' => 'King James Version'
                ]
            ]
        ];

        $expected_filtered_data = [
            'data' => [
                [
                    'abbr' => 'ENGESV',
                    'name' => 'English Standard Version'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test search term
        $request->method( 'get' )
            ->willReturnMap([
                [ 'language_code', '', '' ],
                [ 'paged', 1, 1 ],
                [ 'limit', 50, 50 ],
                [ 'search', '', $search_term ]
            ]);

        // Mock the Bibles service
        $bibles = $this->createMock( Bibles::class );
        $bibles->method( 'all' )
            ->willReturn( $all_bibles );

        // Mock the container to return our mock Bibles service
        $container = container();
        $container->singleton(Bibles::class, function () use ( $bibles ) {
            return $bibles;
        });

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains only the filtered data
        $this->assertCount( 1, $response['data'] );
        $this->assertEquals( 'ENGESV', $response['data'][0]['abbr'] );
    }
}
