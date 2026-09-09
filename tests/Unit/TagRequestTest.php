<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグ名が重複していると新規登録は拒否される(): void
    {
        Tag::create(['name' => '質問']);

        $validator = Validator::make(
            ['name' => '質問'],
            (new StoreTagRequest)->rules()
        );

        $this->assertTrue($validator->fails());
    }

    public function test_タグ名の未入力は拒否される(): void
    {
        $validator = Validator::make([], (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_自分自身の名前を維持したまま更新できる(): void
    {
        $tag = Tag::create(['name' => '質問']);

        Route::put('/test-route/{tag}', fn () => null);
        $request = UpdateTagRequest::create("/test-route/{$tag->id}", 'PUT', ['name' => '質問']);
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request)->bind($request));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_他のタグが使用している名前には更新できない(): void
    {
        Tag::create(['name' => '質問']);
        $tag = Tag::create(['name' => '要望']);

        Route::put('/test-route/{tag}', fn () => null);
        $request = UpdateTagRequest::create("/test-route/{$tag->id}", 'PUT', ['name' => '質問']);
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request)->bind($request));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
    }
}
