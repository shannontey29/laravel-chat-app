<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
	protected $model = Chat::class;

	public function definition(): array
	{
		return [
			'sender_id' => User::factory(),
			'receiver_id' => User::factory(),
			'message' => $this->faker->sentence(6),
			'reply_id' => null,
			'seen_at' => null,
			'message_deleted_at' => null,
		];
	}

	public function seen(): static
	{
		return $this->state(fn () => [
			'seen_at' => now(),
		]);
	}

	public function deleted(): static
	{
		return $this->state(fn () => [
			'message_deleted_at' => now(),
		]);
	}

	public function reply(Chat $original): static
	{
		return $this->state(fn () => [
			'reply_id' => $original->id,
			'sender_id' => $original->receiver_id,
			'receiver_id' => $original->sender_id,
		]);
	}
}

