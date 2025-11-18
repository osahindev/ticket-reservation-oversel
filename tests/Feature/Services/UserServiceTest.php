<?php

namespace Tests\Feature\Services;

use App\Services\UserService;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function test_get_visitor_token_header_name_returns_correct_name(): void
    {
        // Act
        $headerName = $this->userService->getVisitorTokenHeaderName();

        // Assert
        $this->assertEquals("Visitor-Token", $headerName);
    }

    public function test_get_visitor_token_returns_token_from_request_header(): void
    {
        // Arrange
        $expectedToken = Str::uuid()->toString();

        // Act
        $response = $this->withHeaders([
            'Visitor-Token' => $expectedToken,
        ])->get('/');

        $token = $this->userService->getVisitorToken();

        // Assert
        $this->assertEquals($expectedToken, $token);
    }

    public function test_get_visitor_token_returns_null_when_header_not_present(): void
    {
        // Act
        $token = $this->userService->getVisitorToken();

        // Assert
        $this->assertNull($token);
    }

    public function test_create_visitor_token_returns_valid_uuid(): void
    {
        // Act
        $token = $this->userService->createVisitorToken();

        // Assert
        $this->assertNotEmpty($token);
        $this->assertTrue(Str::isUuid($token));
    }

    public function test_create_visitor_token_returns_unique_tokens(): void
    {
        // Act
        $token1 = $this->userService->createVisitorToken();
        $token2 = $this->userService->createVisitorToken();

        // Assert
        $this->assertNotEquals($token1, $token2);
    }
}
