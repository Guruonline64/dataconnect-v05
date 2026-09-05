<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_keeps_legacy_php_path(): void
    {
        $this->getJson('/api/health.php')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_register_returns_token_and_user(): void
    {
        $this->postJson('/api/register.php', [
            'phone'=>'08012345678','username'=>'tester','password'=>'secret123'
        ])->assertStatus(201)
          ->assertJsonStructure(['success','message','token','user'=>['id','phone','username','role']]);
    }
}
