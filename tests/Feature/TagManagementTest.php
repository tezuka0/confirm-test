<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはタグを追加できない(): void
    {
        $response = $this->post('/admin/tags', ['name' => '質問']);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('tags', ['name' => '質問']);
    }

    public function test_認証済みユーザーはタグを追加できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', ['name' => '質問']);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', ['name' => '質問']);
    }

    public function test_編集画面が表示され更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => '質問']);

        $editResponse = $this->actingAs($user)->get("/admin/tags/{$tag->id}/edit");
        $editResponse->assertOk();
        $editResponse->assertSee('質問');

        $updateResponse = $this->actingAs($user)->put("/admin/tags/{$tag->id}", ['name' => '要望']);
        $updateResponse->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '要望']);
    }

    public function test_タグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => '質問']);

        $response = $this->actingAs($user)->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}