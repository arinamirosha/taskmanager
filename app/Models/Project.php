<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'favorite',
        'color',
    ];

    /**
     * Many projects to one user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One project to many tasks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)->withCount('comments');
    }

    /**
     * Many projects has many shared users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shared_users()
    {
        return $this->belongsToMany(User::class, 'shared_projects', 'project_id', 'user_id')
                    ->withPivot('accepted')
                    ->withPivot('favorite');
    }

    public function all_users()
    {
        return $this->shared_users()->where('accepted', true)->get()->merge([$this->user]);
    }

    /**
     * On force delete cascade force delete tasks
     */
    public static function boot() {
        parent::boot();

        static::deleting(function ($project) {
            if ($project->forceDeleting) {
                $project->tasks()->forceDelete();
                $project->shared_users()->detach();
            } else {
                $project->tasks()->delete();
                $project->shared_users()->wherePivot('accepted', null)->detach();
            }
        });
    }
}
