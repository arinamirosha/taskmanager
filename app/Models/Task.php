<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_NORMAL = 1;
    const STATUS_MEDIUM = 2;
    const STATUS_STRONG = 3;

    const STATUS_NEW      = 4;
    const STATUS_PROGRESS = 5;
    const STATUS_FINISHED = 6;

    const STATUS_NEW_TEXT      = 'new';
    const STATUS_PROGRESS_TEXT = 'progress';
    const STATUS_FINISHED_TEXT = 'finished';

    const TOTAL = 'total';

    const ARCHIVE       = 'archive';
    const TODAY         = 'today';
    const NOT_SCHEDULED = 'notScheduled';
    const UPCOMING      = 'upcoming';

    protected $fillable = [
        'user_id',
        'project_id',
        'owner_id',
        'name',
        'details',
        'schedule',
        'importance',
        'status',
    ];

    /**
     * Many tasks to one project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    /**
     * Many tasks to one user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Many tasks to one owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One task to many comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany( Comment::class);
    }

    /**
     * On force delete cascade delete comments
     */
    public static function boot() {
        parent::boot();

        static::deleting(function ($task) {
            if ($task->forceDeleting) {
                $task->comments()->delete();
            }
        });
    }
}
