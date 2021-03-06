<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_guest_cannot_create_a_project()
    {
        $category = Category::factory()->create();
        $data = [
            'title'       => $this->faker->sentence,
            'category_id' => $category->id,
            'description' => $this->faker->paragraph,
            'active'     => $this->faker->boolean($chanceOfGettingTrue = 80),
            'order'       => $this->faker->randomDigit,
            'status'      => $this->faker->randomElement([
                'unknown',
                'open',
                'scheduled',
                'in_development',
                'completed',
                'cancelled',
            ]),
        ];

        $response = $this->json('POST', '/api/projects', $data);
        $response->assertUnauthorized();
    }

    public function test_user_cannot_create_a_project()
    {
        $this->loginAsUser();

        $category = Category::factory()->create();
        $data = [
            'title'       => $this->faker->sentence,
            'category_id' => $category->id,
            'description' => $this->faker->paragraph,
            'active'     => $this->faker->boolean($chanceOfGettingTrue = 80),
            'order'       => $this->faker->randomDigit,
            'status'      => $this->faker->randomElement([
                'unknown',
                'open',
                'scheduled',
                'in_development',
                'completed',
                'cancelled',
            ]),
        ];

        $response = $this->json('POST', '/api/projects', $data);
        $response->assertUnauthorized();
    }

    public function test_admin_can_create_a_project()
    {
        $this->loginAsAdmin();

        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        Storage::fake('photos');
        $file = UploadedFile::fake()->image('photo.jpg');
        $file2 = UploadedFile::fake()->image('photo_2.jpg');
        $data = [
            'title'       => $this->faker->sentence,
            'category_id' => $category->id,
            'description' => $this->faker->paragraph,
            'order'       => $this->faker->randomDigit,
            'status'      => $this->faker->numberBetween(0, 5),
            'active'     => $this->faker->boolean($chanceOfGettingTrue = 80),
            'tags'        => $tags,
            'photos' => [
                0 => $file,
                1 => $file2,
            ],
        ];
        $response = $this->json('POST', '/api/projects', $data);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'id',
            'title',
            'category_id',
            'description',
            'order',
            'status',
            'images',
            'active',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_guest_cannot_edit_a_project()
    {
        $project = Project::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
        ];

        $response = $this->json('PUT', '/api/projects/'.$project->id, $data);
        $response->assertUnauthorized();
    }

    public function test_user_cannot_edit_a_project()
    {
        $this->loginAsUser();

        $project = Project::factory()->create();

        $data = [
            'title' => $this->faker->sentence,
        ];

        $response = $this->json('PUT', '/api/projects/'.$project->id, $data);
        $response->assertUnauthorized();
    }

    public function test_admin_can_edit_a_project()
    {
        $this->loginAsAdmin();

        $project = Project::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $data = [
            'title' => $this->faker->sentence,
            'tags'  => $tags,
        ];

        $response = $this->json('PUT', '/api/projects/'.$project->id, $data);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'id',
            'title',
            'category_id',
            'description',
            'order',
            'status',
            'active',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_guest_cannot_delete_a_project()
    {
        $project = Project::factory()->create();

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertUnauthorized();
    }

    public function test_user_cannot_delete_a_project()
    {
        $this->loginAsUser();

        $project = Project::factory()->create();

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertUnauthorized();
    }

    public function test_admin_can_delete_a_project()
    {
        $this->loginAsAdmin();

        $project = Project::factory()->create();
        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertSuccessful();
        $this->assertSoftDeleted($project);
    }

    public function test_admin_can_restore_a_project()
    {
        $this->loginAsAdmin();
        $project = Project::factory()->softDeleted()->create();
        $response = $this->json('PUT', route('api.project.restore', $project));
        $response->assertSuccessful();
    }

    public function test_admin_can_delete_permanently_a_project()
    {
        $this->loginAsAdmin();
        $project = Project::factory()->softDeleted()->create();
        $response = $this->json('PUT', route('api.project.delete-permanently', $project));
        $response->assertSuccessful();
        $this->assertDeleted($project);
    }

    public function test_get_projects()
    {
        Project::factory()->count(5)->create();
        $response = $this->json('GET', '/api/projects');
        $response->assertSuccessful();
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'category_id',
                'description',
                'order',
                'status',
                'active',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_get_project()
    {
        $project = Project::factory()->create();

        $response = $this->json('GET', '/api/projects/'.$project->id);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'id',
            'title',
            'category_id',
            'description',
            'order',
            'status',
            'active',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_add_and_remove_tag_from_project()
    {
        $project = Project::factory()->create();
        $tag = Tag::factory()->create();
        $project->tags()->attach($tag);
        $this->assertTrue($project->hasTag($tag));
        $project->tags()->detach($tag);
        $this->assertFalse($project->hasTag($tag));
    }
}
