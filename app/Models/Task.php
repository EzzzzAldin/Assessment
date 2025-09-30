<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'due_date',
        'priority',
        'is_completed',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_completed) {
            return 'done';
        }

        $today = Carbon::today();
        $due = Carbon::parse($this->due_date);

        if ($due->isSameDay($today)) {
            return 'due_today';
        }

        if ($due->lessThan($today)) {
            return 'missed';
        }

        return 'upcoming';
    }
}
