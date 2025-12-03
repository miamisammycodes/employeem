<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'location_id' => $this->location_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'manager_id' => $this->manager_id,
            'is_active' => $this->is_active,
            'path' => $this->when($this->relationLoaded('parent'), fn() => $this->getPath()),
            'depth' => $this->when($this->relationLoaded('parent'), fn() => $this->getDepth()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Counts (loaded conditionally)
            'employees_count' => $this->whenCounted('employees'),
            'children_count' => $this->whenCounted('children'),
            
            // Relations (loaded conditionally)
            'company' => new CompanyResource($this->whenLoaded('company')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'parent' => new DepartmentResource($this->whenLoaded('parent')),
            'children' => DepartmentResource::collection($this->whenLoaded('children')),
            'manager' => $this->whenLoaded('manager', function () {
                return $this->manager ? [
                    'id' => $this->manager->id,
                    'employee_number' => $this->manager->employee_number,
                    'full_name' => $this->manager->full_name,
                ] : null;
            }),
        ];
    }
}
