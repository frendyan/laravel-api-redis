<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ReturnSchedule extends Model
{
    use HasFactory, Notifiable;
    public $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'financing_id',
        'pay_date',
        'k_pokok',
        'k_margin',
        'k_total',
        'k_pokok_left',
        'k_margin_left'
    ];
}
