<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_フォーム入力画面にカテゴリーとタグが表示される(): void
    {
        $category = Category::create(['content' => '商品トラブル']);
        $tag = Tag::create(['name' => '質問']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_バリデーション通過時に確認画面が表示される(): void
    {
        $category = Category::create(['content' => '商品トラブル']);

        $response = $this->post('/contacts/confirm', [
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テストです',
        ]);

        $response->assertOk();
        $response->assertSee('山田');
        $response->assertSee($category->content);
    }

    public function test_バリデーション失敗時はリダイレクトされエラーが返る(): void
    {
        $response = $this->post('/contacts/confirm', []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['category_id', 'first_name', 'last_name']);
    }

    public function test_送信するとcontactsとcontact_tagに保存されthanksにリダイレクトされる(): void
    {
        $category = Category::create(['content' => '商品トラブル']);
        $tag = Tag::create(['name' => '質問']);

        $response = $this->post('/contacts', [
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テストです',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('contact.thanks'));

        $this->assertDatabaseHas('contacts', ['email' => 'test@example.com']);
        $contact = Contact::where('email', 'test@example.com')->firstOrFail();
        $this->assertDatabaseHas('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $tag->id]);
    }

    public function test_thanks画面が正常に表示される(): void
    {
        $response = $this->get('/thanks');

        $response->assertOk();
    }
}