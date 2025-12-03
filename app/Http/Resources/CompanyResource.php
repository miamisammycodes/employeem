<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Counts (loaded conditionally)
            'locations_count' => $this->whenCounted('locations'),
            'departments_count' => $this->whenCounted('departments'),
            'employees_count' => $this->whenCounted('employees'),
            'job_titles_count' => $this->whenCounted('jobTitles'),
            'users_count' => $this->whenCounted('users'),
            
            // Relations (loaded conditionally)
            'locations' => LocationResource::collection($this->whenLoaded('locations')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
            'job_titles' => JobTitleResource::collection($this->whenLoaded('jobTitles')),
        ];
    }
}
