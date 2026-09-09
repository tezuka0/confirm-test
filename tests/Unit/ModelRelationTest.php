<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    private function makeContact(Category $category): Contact
    {
        return Contact::create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'テスト',
        ]);
    }

    public function test_カテゴリーから紐づく複数のお問い合わせを取得できる(): void
    {
        $category = Category::create(['content' => 'その他']);
        $this->makeContact($category);
        $this->makeContact($category);

        $this->assertCount(2, $category->contacts);
    }

    public function test_お問い合わせは1つのカテゴリーに属し複数のタグとsyncできる(): void
    {
        $category = Category::create(['content' => 'その他']);
        $contact = $this->makeContact($category);
        $tag1 = Tag::create(['name' => '質問']);
        $tag2 = Tag::create(['name' => '要望']);

        $contact->tags()->sync([$tag1->id, $tag2->id]);

        $this->assertTrue($contact->category->is($category));
        $this->assertCount(2, $contact->fresh()->tags);
    }

    public function test_タグは中間テーブルを介して複数のお問い合わせに紐づく(): void
    {
        $category = Category::create(['content' => 'その他']);
        $contactA = $this->makeContact($category);
        $contactB = $this->makeContact($category);
        $tag = Tag::create(['name' => '質問']);

        $contactA->tags()->attach($tag->id);
        $contactB->tags()->attach($tag->id);

        $this->assertCount(2, $tag->fresh()->contacts);
    }
}
