<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはCSVをダウンロードできない(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }

    public function test_認証済みユーザーはCSVをダウンロードできる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => '商品トラブル']);
        Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}