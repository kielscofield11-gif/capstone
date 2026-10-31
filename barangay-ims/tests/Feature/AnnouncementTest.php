<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private function activeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_secretary_can_create_a_draft_announcement(): void
    {
        $user = $this->activeUser('secretary');

        $response = $this->actingAs($user)->post('/announcements', [
            'title' => 'Water interruption',
            'body' => 'Water will be interrupted on Friday.',
            'category' => 'notice',
        ]);

        $response->assertRedirect('/announcements');
        $this->assertDatabaseHas('announcements', [
            'title' => 'Water interruption',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
    }

    public function test_editor_can_publish_immediately_from_the_form(): void
    {
        $user = $this->activeUser('secretary');

        $this->actingAs($user)->post('/announcements', [
            'title' => 'Barangay assembly',
            'body' => 'Assembly on Saturday at the covered court.',
            'category' => 'event',
            'action_publish' => '1',
        ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Barangay assembly',
            'status' => 'published',
        ]);
    }

    public function test_staff_cannot_publish_even_when_flag_is_sent(): void
    {
        $user = $this->activeUser('staff');

        $this->actingAs($user)->post('/announcements', [
            'title' => 'Staff note',
            'body' => 'Staff should not be able to publish.',
            'category' => 'general',
            'action_publish' => '1',
        ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Staff note',
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_publish_a_draft(): void
    {
        $author = $this->activeUser('staff');
        $admin = $this->activeUser('admin');
        $announcement = Announcement::create([
            'title' => 'Draft post',
            'body' => 'A draft announcement body.',
            'category' => 'general',
            'status' => 'draft',
            'created_by' => $author->id,
        ]);

        $this->actingAs($admin)
            ->patch("/announcements/{$announcement->id}/publish")
            ->assertRedirect();

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'status' => 'published',
        ]);
    }

    public function test_kagawad_cannot_publish_a_draft(): void
    {
        $author = $this->activeUser('staff');
        $kagawad = $this->activeUser('kagawad');
        $announcement = Announcement::create([
            'title' => 'Draft post',
            'body' => 'A draft announcement body.',
            'category' => 'general',
            'status' => 'draft',
            'created_by' => $author->id,
        ]);

        $this->actingAs($kagawad)
            ->patch("/announcements/{$announcement->id}/publish")
            ->assertForbidden();

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'status' => 'draft',
        ]);
    }

    public function test_published_announcements_appear_on_the_dashboard(): void
    {
        Announcement::create([
            'title' => 'Road repair advisory',
            'body' => 'Purok 3 road repair starts Monday.',
            'category' => 'notice',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Announcement::create([
            'title' => 'Hidden draft',
            'body' => 'This should not appear on the dashboard.',
            'category' => 'general',
            'status' => 'draft',
        ]);
        $admin = $this->activeUser('admin');

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Road repair advisory')
            ->assertDontSee('Hidden draft');
    }

    public function test_archived_announcements_are_hidden_from_the_dashboard(): void
    {
        Announcement::create([
            'title' => 'Old advisory',
            'body' => 'No longer relevant.',
            'category' => 'general',
            'status' => 'archived',
            'published_at' => now(),
        ]);
        $admin = $this->activeUser('admin');

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Old advisory');
    }

    public function test_only_admin_can_delete_an_announcement(): void
    {
        $author = $this->activeUser('secretary');
        $admin = $this->activeUser('admin');
        $announcement = Announcement::create([
            'title' => 'To be deleted',
            'body' => 'Delete me.',
            'category' => 'general',
            'status' => 'draft',
            'created_by' => $author->id,
        ]);

        $this->actingAs($author)
            ->delete("/announcements/{$announcement->id}")
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete("/announcements/{$announcement->id}")
            ->assertRedirect('/announcements');

        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);
    }

    public function test_create_validates_required_fields(): void
    {
        $user = $this->activeUser('secretary');

        $this->actingAs($user)->post('/announcements', [
            'title' => '',
            'body' => '',
        ])->assertSessionHasErrors(['title', 'body', 'category']);
    }

    public function test_all_announcement_pages_render_for_admin(): void
    {
        $admin = $this->activeUser('admin');
        $announcement = Announcement::create([
            'title' => 'Render check',
            'body' => 'Body used for render checks.',
            'category' => 'general',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get('/announcements')->assertOk()->assertSee('Render check');
        $this->actingAs($admin)->get('/announcements/create')->assertOk();
        $this->actingAs($admin)->get("/announcements/{$announcement->id}")->assertOk()->assertSee('Render check');
        $this->actingAs($admin)->get("/announcements/{$announcement->id}/edit")->assertOk();
    }

    public function test_staff_can_edit_their_own_draft(): void
    {
        $staff = $this->activeUser('staff');
        $announcement = Announcement::create([
            'title' => 'Staff draft',
            'body' => 'Original body.',
            'category' => 'general',
            'status' => 'draft',
            'created_by' => $staff->id,
        ]);

        $this->actingAs($staff)->put("/announcements/{$announcement->id}", [
            'title' => 'Staff draft updated',
            'body' => 'Updated body.',
            'category' => 'general',
        ])->assertRedirect('/announcements');

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Staff draft updated',
            'status' => 'draft',
        ]);
    }

    public function test_editor_sees_publish_checkbox_on_the_create_form(): void
    {
        $editor = $this->activeUser('secretary');

        $this->actingAs($editor)->get('/announcements/create')
            ->assertOk()
            ->assertSee('Publish immediately');
    }

    public function test_staff_does_not_see_publish_checkbox_on_the_create_form(): void
    {
        $staff = $this->activeUser('staff');

        $this->actingAs($staff)->get('/announcements/create')
            ->assertOk()
            ->assertDontSee('Publish immediately');
    }

    public function test_archived_announcement_can_be_republished_from_the_index(): void
    {
        $admin = $this->activeUser('admin');
        $announcement = Announcement::create([
            'title' => 'Archived post',
            'body' => 'An archived announcement body.',
            'category' => 'general',
            'status' => 'archived',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->get('/announcements')
            ->assertOk()
            ->assertSee(route('announcements.publish', $announcement), false);
    }

    public function test_archived_announcement_can_be_republished_from_the_show_page(): void
    {
        $admin = $this->activeUser('admin');
        $announcement = Announcement::create([
            'title' => 'Archived post',
            'body' => 'An archived announcement body.',
            'category' => 'general',
            'status' => 'archived',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)->get("/announcements/{$announcement->id}")
            ->assertOk()
            ->assertSee(route('announcements.publish', $announcement), false);
    }
}
