<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartRecoverySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'recoveryEnabled' => false,
            'defaultAbandonmentThresholdMinutes' => 60,
            'maxRecoveryAttempts' => 3,
            'cooldownBetweenAttemptsHours' => 24,
            'emailEnabled' => false,
            'emailFromName' => 'KakKay',
            'emailFromAddress' => 'noreply@kakkay.my',
            'emailReplyTo' => null,
            'emailTrackOpens' => false,
            'emailTrackClicks' => false,
            'smsEnabled' => false,
            'smsProvider' => null,
            'smsFromNumber' => null,
            'smsMaxLength' => 160,
            'pushEnabled' => false,
            'pushProvider' => null,
            'pushIconUrl' => null,
            'pushRequireInteraction' => false,
            'sendStartHour' => 9,
            'sendEndHour' => 21,
            'respectUserTimezone' => true,
            'blockedDays' => [],
            'minCartValue' => 1000,
            'maxMessagesPerCustomerPerWeek' => 2,
            'excludeRepeatRecoveries' => true,
            'excludeIfOrderedWithinDays' => 7,
            'customExclusionRules' => [],
        ];

        foreach ($settings as $name => $value) {
            DB::table('settings')->updateOrInsert(
                [
                    'group' => 'cart_recovery',
                    'name' => $name,
                ],
                [
                    'locked' => false,
                    'payload' => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Cart recovery settings seeded successfully.');
    }
}
