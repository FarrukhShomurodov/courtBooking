<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'price_per_hour',
        'description'
    ];

    public function sportTypes(): BelongsToMany
    {
        return $this->belongsToMany(SportType::class, 'coach_sport_type', 'coach_id', 'sport_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stadium(): HasOne
    {
        return $this->hasOne(Stadium::class );
    }
}
