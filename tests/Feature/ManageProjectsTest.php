<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;
use Facades\Tests\Setup\ProjectFactory;

class ManageProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function guests_cannot_create_nor_view_project_s()
    {
        $project = factory(Project::class)->create();

        $this->get('/projects/create')->assertRedirect('login');
        $this->get('/projects')->assertRedirect('login');
        $this->get($project->path())->assertRedirect('login');
        $this->get($project->path() . '/edit')->assertRedirect('login');
        $this->post('/projects', $project->toArray())->assertRedirect('login');
    }

    /** @test */
    public function a_user_can_create_a_project()
    {
        $this->signIn();

        $this->get('/projects/create')->assertStatus(200);

        $this->followingRedirects()
            ->post('/projects', $attributes = factory(Project::class)->raw())
            ->assertSee($attributes['title'])
            ->assertSee($attributes['description'])
            ->assertSee($attributes['notes']);
    }

    /** @test */
    public function a_user_can_update_a_project()
    {
        $project = ProjectFactory::create();

        $this->actingAs($project->owner)->patch(
            $project->path(),
            $attributes = ['title' => 'Changed', 'description' => 'Changed', 'notes' => 'Changed']
        )
            ->assertRedirect($project->path());

        $this->get($project->path() . '/edit')->assertOk();

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function a_user_can_delete_their_project()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::create();

        $this->actingAs($project->owner)
            ->delete($project->path())
            ->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', $project->only('id'));
    }

    /** @test */
    public function unauthorized_users_cannot_delete_projects()
    {
        $project = ProjectFactory::create();

        $this->delete($project->path())->assertRedirect('/login');

        $user = $this->signIn();

        $this->delete($project->path())->assertstatus(403);

        $project->invite($user);

        $this->actingAs($user)->delete($project->path())->assertStatus(403);
    }

    /** @test */
    public function a_user_can_view_their_project()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::create();

        $this->actingAs($project->owner)->get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->description);
    }

    /** @test */
    function a_user_can_see_all_projects_they_have_been_invited_to_on_their_dashboard()
    {
        $project = tap(ProjectFactory::create())->invite($this->signIn());
        $this->get('/projects')->assertSee($project->title);
    }

    /** @test */
    public function a_project_requires_a_title()
    {
        $this->actingAs(factory(User::class)->create());

        $attributes = factory(Project::class)->raw(['title' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_project_requires_a_description()
    {
        $this->actingAs(factory(User::class)->create());

        $attributes = factory(Project::class)->raw(['description' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('description');
    }

    /** @test */
    public function an_authenticated_user_cannot_view_others_projects()
    {
        $this->be(factory(User::class)->create());

        $project = factory(Project::class)->create();

        $this->get($project->path())->assertStatus(403);
    }

    /** @test */
    public function an_authenticated_user_cannot_update_others_projects()
    {
        $this->be(factory(User::class)->create());

        $project = factory(Project::class)->create();

        $this->patch($project->path())->assertStatus(403);
    }
}
