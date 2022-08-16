<?php

namespace App\Models;

class Bank extends Model
{
    protected $fillable = [
        'place', 'reg_number', 'name', 'city', 'place_active', 'credits', 'license', 'license_status', 'contacts', 'comments'
    ];
}