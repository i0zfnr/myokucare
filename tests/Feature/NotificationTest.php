<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Job;
use App\Models\Oku;
use App\Models\User;
use App\Models\WelfareApplication;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welfare_status_change_notifies_only_the_application_owner(): void
    {
        $ownerOku = $this->oku('Owner', '900101011111');
        $otherOku = $this->oku('Other', '900101012222');
        $owner = User::factory()->create(['role' => 'oku_user', 'oku_id' => $ownerOku->id]);
        $other = User::factory()->create(['role' => 'oku_user', 'oku_id' => $otherOku->id]);
        $officer = User::factory()->create(['role' => 'jkm_officer']);
        $application = WelfareApplication::query()->create([
            'oku_id' => $ownerOku->id, 'application_type' => 'Bantuan Am',
            'application_date' => today(), 'status' => 'Pending',
        ]);

        $this->actingAs($officer)->put(route('welfare.update-status', $application), [
            'status' => 'Approved',
        ])->assertRedirect();

        $this->assertCount(1, $owner->fresh()->notifications);
        $this->assertCount(0, $other->fresh()->notifications);
        $this->assertSame('welfare', $owner->fresh()->notifications->first()->data['category']);
    }

    public function test_new_job_interest_notifies_the_owning_employer_once(): void
    {
        $employer = Employer::query()->create([
            'company_name' => 'Inclusive Sdn Bhd', 'registration_number' => 'REG-1',
            'address' => 'Besut', 'industry_sector' => 'Services', 'contact_person' => 'Manager',
            'phone_number' => '0123456789', 'email' => 'employer@example.test', 'is_active' => true,
        ]);
        $employerUser = User::factory()->create(['role' => 'employer', 'employer_id' => $employer->id]);
        $oku = $this->oku('Candidate', '900101013333');
        $candidate = User::factory()->create(['role' => 'oku_user', 'oku_id' => $oku->id]);
        $job = Job::query()->create([
            'employer_id' => $employer->id, 'title' => 'Kerani', 'description' => 'Tugas pejabat',
            'requirements' => 'SPM', 'oku_category_suitable' => 'Semua', 'salary_min' => 1500,
            'location' => 'Besut', 'employment_type' => 'Sepenuh Masa', 'is_active' => true,
        ]);

        $this->actingAs($candidate)->post(route('jobs.interest', $job))->assertRedirect();
        $this->post(route('jobs.interest', $job))->assertRedirect();

        $this->assertCount(1, $employerUser->fresh()->notifications);
    }

    public function test_user_can_read_own_notification_but_not_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $owner->notify(new SystemNotification('notifications.default_title', 'notifications.default_message', [], route('dashboard'), 'account'));
        $other->notify(new SystemNotification('notifications.default_title', 'notifications.default_message', [], route('dashboard'), 'account'));
        $ownerNotification = $owner->fresh()->notifications->first();
        $otherNotification = $other->fresh()->notifications->first();

        $this->actingAs($owner)->get(route('notifications.read', $ownerNotification))
            ->assertRedirect(route('dashboard'));
        $this->assertNotNull($ownerNotification->fresh()->read_at);

        $this->get(route('notifications.read', $otherNotification))->assertNotFound();
    }

    public function test_mark_all_read_only_updates_current_users_notifications(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        foreach ([$owner, $owner, $other] as $user) {
            $user->notify(new SystemNotification('notifications.default_title', 'notifications.default_message', [], route('dashboard'), 'account'));
        }

        $this->actingAs($owner)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $other->fresh()->unreadNotifications()->count());
    }

    private function oku(string $name, string $nric): Oku
    {
        return Oku::query()->create([
            'name' => $name, 'ic_number' => $nric, 'gender' => 'Lelaki', 'age' => 30,
            'marital_status' => 'Bujang', 'address' => 'Besut', 'education_level' => 'SPM',
            'oku_card_number' => 'OKU-'.$nric, 'oku_category' => 'Fizikal',
            'employment_status' => 'Tidak Bekerja', 'profile_reviewed_at' => now(),
        ]);
    }
}
