<?php

namespace App\Http\Requests\Site;

use App\Enums\ContactTopic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    /**
     * Mirrors resources/js/lib/contactSchema.ts. `website` is a honeypot that humans never fill.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'topic' => ['required', Rule::enum(ContactTopic::class)],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'website' => ['prohibited'],
        ];
    }
}
