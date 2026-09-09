<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function rules(): array
    {
        return (new ExportContactRequest)->rules();
    }

    public function test_正しいフィルタ条件は全て通過する(): void
    {
        $category = Category::create(['content' => 'その他']);

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2024-01-01',
        ], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_フィルタ条件なし_全項目nullable_でも通過する(): void
    {
        $validator = Validator::make([], $this->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_不正な性別の値は拒否される(): void
    {
        $validator = Validator::make(['gender' => 9], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    public function test_存在しないカテゴリ_i_dは拒否される(): void
    {
        $validator = Validator::make(['category_id' => 9999], $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }
}
