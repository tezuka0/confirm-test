<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_一覧がJSON形式で返る(): void
    {
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

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [['id', 'category', 'first_name', 'last_name', 'tags']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    public function test_存在しないIDの詳細は404でエラーJSONが返る(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'お問い合わせが見つかりませんでした。']);
    }

    public function test_新規作成すると201で作成される(): void
    {
        $category = Category::create(['content' => '商品トラブル']);

        $response = $this->postJson('/api/v1/contacts', [
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['email' => 'test@example.com']);
    }

    public function test_バリデーションエラー時は422が返る(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);
    }

    public function test_更新すると200でタグがsyncされる(): void
    {
        $category = Category::create(['content' => '商品トラブル']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'category_id' => $category->id,
            'first_name' => '鈴木',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'first_name' => '鈴木']);
    }

    public function test_削除すると204が返る(): void
    {
        $category = Category::create(['content' => '商品トラブル']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}