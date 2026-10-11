<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportSongRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'max:100000'],
            'original_source' => ['nullable', 'string', 'max:100000'],
            'title' => [$this->routeIs('song-drafts.store') ? 'required' : 'nullable', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:30'],
            'bpm' => ['nullable', 'integer', 'between:20,400'],
            'capo' => ['nullable', 'integer', 'between:0,24'],
            'meter' => ['nullable', 'string', 'max:10', 'regex:~^(?:[1-9]|[12][0-9]|3[0-2])/(?:1|2|4|8|16|32)$~'],
            'source_url' => ['nullable', 'url:http,https', 'max:2048'],
            'review_token' => [$this->routeIs('song-drafts.store') ? 'required' : 'nullable', 'string', 'max:4096'],
            'confirmed' => [$this->routeIs('song-drafts.store') ? 'accepted' : 'nullable'],
        ];
    }
}
