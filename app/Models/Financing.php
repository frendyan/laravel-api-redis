<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Financing extends Model
{
    use HasFactory, Notifiable;
    public $primaryKey = 'id';
    protected $guarded = [];

    protected $fillable = [
        'financing_id',
        'financing_amount',
        'yearly_margin',
        'tenor',
        'main_payment_periode',
        'margin_payment_periode',
        'financing_start_date',
        'financing_due_date'
    ];
}
