<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobTitleResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'level' => $this->level,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'salary_range' => $this->salary_range,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Counts (loaded conditionally)
            'employees_count' => $this->whenCounted('employees'),
            
            // Relations (loaded conditionally)
            'company' => new CompanyResource($this->whenLoaded('company')),
        ];
    }
}
