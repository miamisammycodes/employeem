<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'full_address' => $this->full_address,
            'timezone' => $this->timezone,
            'is_headquarters' => $this->is_headquarters,
            'phone' => $this->phone,
            'email' => $this->email,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Counts (loaded conditionally)
            'departments_count' => $this->whenCounted('departments'),
            'employees_count' => $this->whenCounted('employees'),
            
            // Relations (loaded conditionally)
            'company' => new CompanyResource($this->whenLoaded('company')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
        ];
    }
}
