<?php

namespace Tests\Unit\Api;

use App\Http\Requests\Api\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function rules(): array
    {
        return (new StoreContactRequest)->rules();
    }

    public function test_全ての必須項目とタグ入力を受け付ける(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => '質問']);

        $validator = Validator::make([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_必須項目が空だと拒否される(): void
    {
        $validator = Validator::make([], $this->rules());

        $this->assertTrue($validator->fails());
        foreach (['category_id', 'first_name', 'last_name', 'gender', 'email', 'tel', 'address', 'detail'] as $field) {
            $this->assertArrayHasKey($field, $validator->errors()->toArray());
        }
    }

    public function test_電話番号がハイフンを含む場合は拒否される(): void
    {
        $category = Category::create(['content' => 'その他']);

        $validator = Validator::make([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'お問い合わせ内容です。',
        ], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }

    public function test_存在しないタグ_i_dは拒否される(): void
    {
        $category = Category::create(['content' => 'その他']);

        $validator = Validator::make([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [9999],
        ], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tag_ids.0', $validator->errors()->toArray());
    }
}
