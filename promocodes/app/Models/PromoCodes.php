<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodes extends Model
{
    use HasFactory;
    public static $active = 1;
    static $deactivated = 2;
    protected $fillable = [
        'promocode',
        'no_rides',
        'expiry_date',
        'status',
        'venue_id',
        'acceptable_radius',
        'created_at',
        'updated_at'
    ];

    public function checkIfCodeIsValid() {
        if ($this->expiry_date != null &&
            $this->expiry_date >= Carbon::now() &&
            $this->status == self::$active) {
                return true;
            } else {
                return false;
            }
    }
}
