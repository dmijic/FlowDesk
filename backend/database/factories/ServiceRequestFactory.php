<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\RequestStatus;
use App\Models\Department;
use App\Models\RequestType;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type_id' => RequestType::factory(),
            'department_id' => Department::factory(),
            'priority' => fake()->randomElement(array_map(fn (Priority $value) => $value->value, Priority::cases())),
            'status' => RequestStatus::DRAFT,
            'created_by' => User::factory(),
            'submitted_at' => null,
            'decided_at' => null,
        ];
    }
}
