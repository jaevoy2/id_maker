<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'barangay',
        'municipality',
        'age',
        'contact',
        'lrn',
        'emergency_contact',
        'birthdate',
        'signature',
        'image',
        'qr_code',
        'year_level',
        'section_id',
        'strand_id',
    ];

    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function strand() {
        return $this->belongsTo(Strand::class);
    }

    public function subjects() {
        return $this->belongsToMany(Subject::class, 'subject_students');
    }

}
