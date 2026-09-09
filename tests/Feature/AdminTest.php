<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeContact(Category $category, array $overrides = []): Contact
    {
        return Contact::create(array_merge([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ], $overrides));
    }

    public function test_未認証ユーザーは管理画面にアクセスできずログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_認証済みユーザーは管理画面を表示できる(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => '商品トラブル']);
        $this->makeContact($category);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee('山田');
    }

    public function test_キーワード検索で絞り込める(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => '商品トラブル']);
        $this->makeContact($category, ['first_name' => '山田', 'email' => 'yamada@example.com']);
        $this->makeContact($category, ['first_name' => '鈴木', 'email' => 'suzuki@example.com']);

        $response = $this->actingAs($user)->get('/admin?keyword=鈴木');

        $response->assertOk();
        $response->assertSee('鈴木');
        $response->assertDontSee('山田');
    }

    public function test_詳細画面にカテゴリー情報付きで表示される(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => '商品トラブル']);
        $contact = $this->makeContact($category);

        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertSee('山田');
        $response->assertSee($category->content);
    }

    public function test_削除すると一覧にリダイレクトされレコードが消える(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => '商品トラブル']);
        $contact = $this->makeContact($category);

        $response = $this->actingAs($user)->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
