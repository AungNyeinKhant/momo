<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'suppliers'; // Adjust the table name as needed
    protected $fillable = ['image','name', 'link']; // List of columns that can be mass-assigned
}
