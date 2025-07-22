<?php

namespace Tests\Controllers;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Controllers\BibleController;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Requests\RequestInterface;
use Tests\TestCase;
use function CodeZone\Bible\set_plugin_option;

/**
 * @group controllers
 * @group settings
 * @group biblebrains
 * @group bibles
 */
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
        $request->expects( $this->any() )
            ->method( 'get' )
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
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'id', null, $bible_id ]
            ]);

        // Mock the BibleBrainsBibles service
        $bibles = $this->createMock( BibleBrainsBibles::class );
        $bibles->method( 'find' )
            ->with( $bible_id )
            ->willReturn( $expected_data );

        // Mock the container to return our mock BibleBrainsBibles service
        $this->mock_api_response('bibles/' . $bible_id, new Response(200, [], json_encode([
            'data' => $expected_data
        ])));

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
            [
                'abbr' => 'ENGESV',
                'name' => 'English Standard Version'
            ],
            [
                'abbr' => 'ENGKJV',
                'name' => 'King James Version'
            ]
        ];

        $expected_options = [
            [
                'value' => 'ENGESV',
                'itemText' => 'English Standard Version'
            ],
            [
                'value' => 'ENGKJV',
                'itemText' => 'King James Version'
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Mock the container to return our mock BibleBrainsBibles service
        $this->mock_api_response( 'bibles', new Response( 200, [], json_encode( $bible_data ) ) );

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
        $language_id = '6414';
        $expected_data = [
            [
                'abbr' => 'ENGESV',
                'name' => 'English Standard Version'
            ],
            [
                'abbr' => 'ENGKJV',
                'name' => 'King James Version'
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test language_code
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'language', '', $language_id ],
                [ 'paged', 1, 1 ],
                [ 'limit', 50, 50 ],
                [ 'search', '', '' ]
            ]);

        // Mock the container to return our mock BibleBrainsBibles service
        $this->mock_api_response( 'bibles', new Response( 200, [], json_encode( $expected_data ) ) );

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        foreach ($expected_data as $i => $bible) {
            $result_item = $response[$i];
            $this->assertEquals($bible['abbr'], $result_item['code']);
        }
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

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test search term
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'language_code', '', '' ],
                [ 'paged', 1, 1 ],
                [ 'limit', 50, 50 ],
                [ 'search', '', $search_term ]
            ]);

        // Mock the container to return our mock BibleBrainsBibles service
        $this->mock_api_response( 'bibles', new Response( 200, [], json_encode( $all_bibles ) ) );

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains only the filtered data
        $this->assertCount( 1, $response );
        $this->assertEquals( 'ENGESV', $response[0]['code'] );
    }
}
