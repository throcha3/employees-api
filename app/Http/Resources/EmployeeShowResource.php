<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'manager_id' => $this->manager_id,
            'manager_name' => $this->manager->name,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'city' => $this->city,
            'state' => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
