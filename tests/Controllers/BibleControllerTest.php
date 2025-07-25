<?php

namespace Tests\Controllers;

use CodeZone\Bible\Controllers\BibleController;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Requests\RequestInterface;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use Tests\TestCase;

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

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Configure the mock to return the test bible_id
        $request->expects( $this->any() )
            ->method( 'get' )
            ->willReturnMap([
                [ 'id', null, $bible_id ]
            ]);

        // Create the controller
        $controller = new BibleController();

        // Call the show method
        $response = $controller->show( $request );

        // Assert that the response contains the expected data
        $this->assertEquals( $response['abbr'], $bible_id );
    }

    /**
     * @test
     */
    public function it_returns_bibles_as_options()
    {
        $expected_options = [
            [
                'value' => 'ENGESV',
                'itemText' => 'English Standard Version',
                'source' => 'bible-brains'
            ],
            [
                'value' => 'ENGKJV',
                'itemText' => 'King James Version',
                'source' => 'bible-brains'
            ]
        ];

        // Create a mock Request object
        $request = $this->createMock( RequestInterface::class );

        // Create the controller
        $controller = new BibleController();

        // Call the options method
        $response = $controller->options( $request );

        // Assert that the response contains the expected options
        foreach ( $expected_options as $expected ) {
            $found = false;
            foreach ( $response as $actual ) {
                if ( $actual['value'] === $expected['value'] &&
                   $actual['itemText'] === $expected['itemText'] ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Option with value '{$expected['value']}' and text '{$expected['itemText']}' not found"
            );

        }
    }

    /**
     * @test
     * @group debug
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

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains the expected options
        foreach ( $expected_data as $expected ) {
            $found = false;
            foreach ( $response as $actual ) {
                if ( $actual['code'] === $expected['abbr'] ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(
                $found,
                "Option with value '{$expected['abbr']}' and text '{$expected['name']}' not found"
            );
        }
    }

    /**
     * @test
     */
    public function it_filters_bibles_by_search_term()
    {
        // Create test data
        $search_term = 'Standard';

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

        // Create the controller
        $controller = new BibleController();

        // Call the index method
        $response = $controller->index( $request );

        // Assert that the response contains only the filtered data
        $this->assertCount( 1, $response );
        $this->assertEquals( 'ENGESV', $response[0]['code'] );
    }
}
