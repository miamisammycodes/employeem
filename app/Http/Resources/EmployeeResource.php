<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'job_title_id' => $this->job_title_id,
            'location_id' => $this->location_id,
            'employee_number' => $this->employee_number,

            // User info
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'avatar' => $this->user?->avatar,

            // Employment details
            'hire_date' => $this->hire_date?->toDateString(),
            'probation_end_date' => $this->probation_end_date?->toDateString(),
            'status' => $this->status,
            'employment_type' => $this->employment_type,
            'work_email' => $this->work_email,
            'work_phone' => $this->work_phone,

            // Salary info (conditionally included based on authorization)
            'salary' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                $this->salary
            ),
            'pay_frequency' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                $this->pay_frequency
            ),
            'bank_account_number' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                $this->bank_account_number
            ),
            'bank_name' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                $this->bank_name
            ),
            'tax_id' => $this->when(
                $request->user()?->can('viewSalary', $this->resource),
                $this->tax_id
            ),

            // Personal info
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,

            // Address
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,

            // Contact
            'personal_email' => $this->personal_email,
            'personal_phone' => $this->personal_phone,

            // Termination
            'termination_date' => $this->termination_date?->toDateString(),
            'termination_reason' => $this->termination_reason,

            // Computed
            'full_name' => $this->full_name,
            'is_on_probation' => $this->isOnProbation(),
            'is_terminated' => $this->isTerminated(),
            'years_of_service' => $this->hire_date ? $this->getYearsOfService() : null,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),

            // Relations (loaded conditionally)
            'user' => new UserResource($this->whenLoaded('user')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'job_title' => new JobTitleResource($this->whenLoaded('jobTitle')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'managers' => EmployeeResource::collection($this->whenLoaded('managers')),
            'direct_reports' => EmployeeResource::collection($this->whenLoaded('directReports')),
            'emergency_contacts' => EmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),

            // Counts
            'direct_reports_count' => $this->whenCounted('directReports'),
        ];
    }
}
