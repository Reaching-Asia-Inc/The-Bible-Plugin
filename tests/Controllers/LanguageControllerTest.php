<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use CodeZone\Bible\Services\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\container;

class LanguageControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_language_by_id()
    {
        // Create test data
        $language_id = '6414';
        $expected_data = [
            'id' => '6414',
            'name' => 'English',
            'autonym' => 'English',
            'code' => 'eng'
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test language_id
        $request->method( 'get' )
            ->willReturnMap([
                [ 'id', null, $language_id ]
            ]);

        // Mock the Languages service
        $languages = $this->createMock( Languages::class );
        $languages->method( 'find' )
            ->with( $language_id )
            ->willReturn( $expected_data );

        // Mock the container to return our mock Languages service
        $container = container();
        $container->singleton(Languages::class, function () use ( $languages ) {
            return $languages;
        });

        // Create the controller
        $controller = new LanguageController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }

    /**
     * @test
     */
    public function it_returns_languages_as_options()
    {
        // Create test data
        $language_data = [
            'data' => [
                [
                    'id' => '6414',
                    'name' => 'English',
                    'autonym' => 'English',
                    'code' => 'eng'
                ],
                [
                    'id' => '6415',
                    'name' => 'Spanish',
                    'autonym' => 'Español',
                    'code' => 'spa'
                ]
            ]
        ];

        $expected_options = [
            'data' => [
                [
                    'value' => 'eng',
                    'itemText' => 'English'
                ],
                [
                    'value' => 'spa',
                    'itemText' => 'Spanish'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Mock the Languages service
        $languages = $this->createMock( Languages::class );
        $languages->method( 'all' )
            ->willReturn( $language_data );
        $languages->method( 'as_options' )
            ->with( $language_data['data'] )
            ->willReturn( $expected_options['data'] );

        // Mock the container to return our mock Languages service
        $container = container();
        $container->singleton(Languages::class, function () use ( $languages ) {
            return $languages;
        });

        // Create the controller
        $controller = new LanguageController();

        // Call the options method
        $response = $controller->options( $request );

        // Assert that the response contains the expected options
        $this->assertEquals( $expected_options, $response );
    }

    /**
     * @test
     */
    public function it_returns_all_languages()
    {
        // Create test data
        $expected_data = [
            'data' => [
                [
                    'id' => '6414',
                    'name' => 'English',
                    'autonym' => 'English',
                    'code' => 'eng'
                ],
                [
                    'id' => '6415',
                    'name' => 'Spanish',
                    'autonym' => 'Español',
                    'code' => 'spa'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return pagination parameters
        $request->method( 'get' )
            ->willReturnMap([
                [ 'search', null, null ],
                [ 'paged', 1, 1 ],
                [ 'limit', 50, 50 ]
            ]);

        // Mock the Languages service
        $languages = $this->createMock( Languages::class );
        $languages->method( 'all' )
            ->with( [ 'page' => 1, 'limit' => 50 ] )
            ->willReturn( $expected_data );

        // Mock the container to return our mock Languages service
        $container = container();
        $container->singleton(Languages::class, function () use ( $languages ) {
            return $languages;
        });

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
        $search_term = 'English';
        $expected_data = [
            'data' => [
                [
                    'id' => '6414',
                    'name' => 'English',
                    'autonym' => 'English',
                    'code' => 'eng'
                ]
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the search term
        $request->method( 'get' )
            ->willReturnMap([
                [ 'search', null, $search_term ]
            ]);

        // Mock the Languages service
        $languages = $this->createMock( Languages::class );
        $languages->method( 'search' )
            ->with( $search_term )
            ->willReturn( $expected_data );

        // Mock the container to return our mock Languages service
        $container = container();
        $container->singleton(Languages::class, function () use ( $languages ) {
            return $languages;
        });

        // Create the controller
        $controller = new LanguageController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $expected_data, $response );
    }
}
