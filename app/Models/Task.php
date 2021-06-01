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

    const STATUS_NEW = 4;
    const STATUS_PROGRESS = 5;
    const STATUS_FINISHED = 6;

    const ARCHIVE = 'archive';
    const TODAY = 'today';
    const NOT_SCHEDULED = 'notScheduled';
    const UPCOMING = 'upcoming';

    protected $fillable = [
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
}
