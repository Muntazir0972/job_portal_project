<?php

namespace Database\Factories;
use App\Models\Job_Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job_Type>
 */
class Job_TypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *    protected $model = Job_Type::class;

     * @return array<string, mixed>
     */
    protected $model = Job_Type::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name()
            
        ];
    }
}
