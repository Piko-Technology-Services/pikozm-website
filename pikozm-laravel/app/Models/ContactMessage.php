<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * "Don't be shy, get in touch" enquiries from the home page.
 */
class ContactMessage extends Model
{
    public const SERVICES = [
        'Business Consultancy',
        'Finance Systems',
        'Accounting Software',
        'Web Development',
        'CS Training',
        'Graphics & Motion',
        'Large Format Printing',
        'Bespoke Software',
    ];

    protected $fillable = ['name', 'email', 'services', 'message'];

    protected function casts(): array
    {
        return ['services' => 'array'];
    }

    public function toWhatsAppText(): string
    {
        $services = $this->services ?: ['(none selected)'];

        return "Hello, my name is {$this->name}.\n\n"
            ."Email: {$this->email}\n\n"
            ."I'm interested in the following services:\n- ".implode("\n- ", $services)."\n\n"
            ."Message:\n{$this->message}";
    }
}
