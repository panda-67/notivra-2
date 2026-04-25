<?php

namespace App\Domains\Hire\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $step = (int) $this->input('step', 1);

        return match ($step) {
            1 => [
                'step' => 'required|integer',
                'service_id' => 'required|uuid|exists:services,id',
                'topic' => 'required|string|max:500',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|numeric|max:20',
                'description' => 'nullable|string',
            ],
            2 => [
                'step' => 'required|integer',
                'output' => 'required|string|max:125',
                'field' => 'nullable|string|max:255',
                'level' => 'nullable|string|max:100',
                'institution' => 'nullable|string|max:255',
                'files' => 'nullable|file|mimes:pdf,xlsx,csv,zip|max:2024', // Max 2MB
            ],
            3 => [
                'step' => 'required|integer',
                'deadline' => 'required|date|after:today',
                'budget' => 'nullable|numeric|min:0',
                'willing_dp' => 'nullable|boolean',
                'address' => 'nullable|string|max:225',
                'city' => 'nullable|string|max:55',
                'postal_code' => 'nullable|numeric|max:5',
            ],
            default => [
                'step' => 'required|integer',
            ],
        };
    }

    /**
     * Custom pesan error (opsional)
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Silakan pilih layanan terlebih dahulu.',
            'deadline.after' => 'Deadline harus diatur untuk hari esok atau setelahnya.',
            'budget.numeric' => 'Anggaran harus berupa angka.',
        ];
    }
}
