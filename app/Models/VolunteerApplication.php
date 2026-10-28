<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "Be Part of the Team" applications from the Digital Impact page.
 * Uploaded files are stored on the private "local" disk.
 */
class VolunteerApplication extends Model
{
    public const AVAILABILITY = [
        'remote' => 'Remote Only',
        'onsite' => 'Onsite Only',
        'hybrid' => 'Hybrid (Remote & Onsite)',
        'flexible' => 'Flexible / Open to Any',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'country', 'skills', 'availability',
        'message', 'id_document_path', 'resume_path', 'status',
    ];
}
