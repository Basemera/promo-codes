<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCodes extends Model
{
    use HasFactory;
    const ACTIVE = 1;
    const DEACTIVATED = 0;
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

    /**
     * Check if code is valid
     *
     * @return boolean
     */
    public function checkIfCodeIsValid() {
        if ($this->expiry_date != null &&
            $this->expiry_date >= Carbon::now() &&
            $this->status == self::ACTIVE) {
                return true;
            } else {
                return false;
            }
    }

    /**
     * Check if code is expired
     *
     * @return boolean
     */
    public function checkIfCodeIsExpired() {
        if ($this->expiry_date &&
        $this->expiry_date >= Carbon::now()) {
            return false;
        } else {
            return true;
        }
    }

}
