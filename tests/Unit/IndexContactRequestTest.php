<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function rules(): array
    {
        return (new IndexContactRequest)->rules();
    }

    public function test_キーワード_性別_カテゴリ_日付フィルタは有効(): void
    {
        $category = Category::create(['content' => 'その他']);

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 2,
            'category_id' => $category->id,
            'date' => '2024-01-01',
        ], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_性別0_すべて_は許容される(): void
    {
        $validator = Validator::make(['gender' => 0], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_不正な性別値は拒否される(): void
    {
        $validator = Validator::make(['gender' => 9], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_条件なしでも通過する(): void
    {
        $validator = Validator::make([], $this->rules());

        $this->assertFalse($validator->fails());
    }
}
