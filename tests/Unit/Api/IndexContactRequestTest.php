<?php

namespace Tests\Unit\Api;

use App\Http\Requests\Api\IndexContactRequest;
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

    public function test_キーワード_性別_カテゴリ_日付_per_pageは有効(): void
    {
        $category = Category::create(['content' => 'その他']);

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2024-01-01',
            'per_page' => 20,
        ], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_性別0は許容されない_1から3のみ(): void
    {
        $validator = Validator::make(['gender' => 0], $this->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_per_pageが100を超えると拒否される(): void
    {
        $validator = Validator::make(['per_page' => 101], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    public function test_存在しないカテゴリ_i_dは拒否される(): void
    {
        $validator = Validator::make(['category_id' => 9999], $this->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_条件なしでも通過する(): void
    {
        $validator = Validator::make([], $this->rules());

        $this->assertFalse($validator->fails());
    }
}
