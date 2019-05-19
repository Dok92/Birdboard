<?php

namespace App;

use App\Task;
use App\User;
use App\Activity;
use App\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use RecordsActivity;

    protected $guarded = [];

    public function path()
    {
        return "/projects/{$this->id}";
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Add a task to the project.
     *
     * @param array $tasks
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addTasks($tasks)
    {
        return $this->tasks()->createMany($tasks);
    }

    /**
     * Add a task to the project.
     *
     * @param  string $body
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function addTask($body)
    {
        return $this->tasks()->create(compact('body'));
    }

    public function activity()
    {
        return $this->hasMany(Activity::class)->latest();
    }

    /**
     * Invite a user to the project.
     *
     * @param \App\User $user
     */
    public function invite(User $user)
    {
        $this->members()->attach($user);
    }

    /**
     * Get all members that are assigned to the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')->withTimestamps();
    }
}
