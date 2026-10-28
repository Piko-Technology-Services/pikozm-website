<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "Join Our Team" registrations from the Talent page.
 */
class TalentApplication extends Model
{
    public const SKILLS = [
        'Web Development',
        'Graphic Design',
        'Marketing / Social Media',
        'Content Creation',
        'Other',
    ];

    protected $fillable = ['full_name', 'phone', 'email', 'skill', 'message'];

    public function toWhatsAppText(): string
    {
        return "Hello Piko Tech 👋,\nI’m interested in joining your team!\n\n"
            ."👤 Name: {$this->full_name}\n"
            ."📱 Phone: {$this->phone}\n"
            .'📧 Email: '.($this->email ?: 'N/A')."\n"
            ."💡 Skill Area: {$this->skill}\n"
            .'📝 Note: '.($this->message ?: 'N/A');
    }
}
