<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
        return             [
            'service_id'    => 'required|exists:services,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'topic'         => 'required|string|max:255',
            'output'        => 'required|string|max:255',
            'deadline'      => 'required|date|after:today',
            'budget'        => 'required|numeric|min:300000',
            'field'         => 'required|string|max:255',
            'phone'         => 'nullable|numeric|max:20',
            'institution'   => 'nullable|string|max:255',
            'level'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'willing_dp'    => 'nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'service_id' => 'Layanan',
            'topic'      => 'Topik/Judul',
            'field'      => 'Bidang kajian',
            'budget'     => 'Anggaran',
        ];
    }

    public function messages()
    {
        return [
            'field.required'      => ':attribute wajib diisi agar kami tahu fokus kajian Anda.',
            'topic.required'      => ':attribute wajib diisi.',
            'budget.min'          => ':attribute minimum adalah Rp 300.000.',
            'deadline.after'      => ':attribute harus tanggal setelah hari ini.',
            'service_id.required' => 'Mohon pilih layanan yang tersedia.',
            'email.email'         => 'Format email yang Anda masukkan tidak valid.',
        ];
    }
}
