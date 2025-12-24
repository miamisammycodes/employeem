<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $companyId = $employee->company_id;

        return [
            // User account fields
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($employee->user_id),
            ],

            // Employment details
            'employee_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')
                    ->where('company_id', $companyId)
                    ->ignore($employee->id),
            ],
            'department_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'job_title_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('job_titles', 'id')->where('company_id', $companyId),
            ],
            'location_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'hire_date' => ['sometimes', 'required', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after:hire_date'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'on_leave', 'terminated', 'suspended'])],
            'employment_type' => ['sometimes', 'required', Rule::in(['full_time', 'part_time', 'contract', 'intern', 'temporary'])],
            'work_email' => ['nullable', 'email', 'max:255'],
            'work_phone' => ['nullable', 'string', 'max:50'],

            // Salary information
            'salary' => ['nullable', 'numeric', 'min:0'],
            'pay_frequency' => ['nullable', Rule::in(['weekly', 'bi_weekly', 'semi_monthly', 'monthly', 'annually'])],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50'],

            // Personal information
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed', 'domestic_partnership'])],
            'nationality' => ['nullable', 'string', 'max:100'],

            // Address
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            // Contact
            'personal_email' => ['nullable', 'email', 'max:255'],
            'personal_phone' => ['nullable', 'string', 'max:50'],

            // Managers
            'manager_ids' => ['nullable', 'array'],
            'manager_ids.*' => [
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'primary_manager_id' => [
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],

            // Termination
            'termination_date' => ['nullable', 'date'],
            'termination_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'department_id.exists' => 'The selected department does not exist or does not belong to your company.',
            'job_title_id.exists' => 'The selected job title does not exist or does not belong to your company.',
            'location_id.exists' => 'The selected location does not exist or does not belong to your company.',
            'manager_ids.*.exists' => 'One or more selected managers do not exist or do not belong to your company.',
            'primary_manager_id.exists' => 'The selected primary manager does not exist or does not belong to your company.',
        ];
    }
}
