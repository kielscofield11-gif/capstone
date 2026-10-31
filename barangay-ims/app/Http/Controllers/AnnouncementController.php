<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Traits\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    use LogsAudit;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Announcement::class);

        $request->validate([
            'status' => 'nullable|in:draft,published,archived',
            'category' => 'nullable|in:'.implode(',', array_keys(Announcement::CATEGORIES)),
        ]);

        $query = Announcement::with('author')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $announcements = $query->paginate(10)->withQueryString();
        $categories = Announcement::CATEGORIES;

        return view('announcements.index', compact('announcements', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);
        $categories = Announcement::CATEGORIES;

        return view('announcements.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $validated = $this->validated($request);
        $validated['created_by'] = Auth::id();

        $announcement = DB::transaction(function () use ($validated, $request) {
            $announcement = Announcement::create($validated);

            if ($this->shouldPublish($request)) {
                $announcement->update([
                    'status' => Announcement::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]);
            }

            return $announcement;
        });

        $this->auditCreated($announcement, "Created announcement \"{$announcement->title}\"");
        if ($announcement->isPublished()) {
            $this->auditEvent('announcement_published', $announcement, "Published announcement \"{$announcement->title}\"");
        }

        return redirect()->route('announcements.index')
            ->with('success', $announcement->isPublished() ? 'Announcement published.' : 'Draft saved.');
    }

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        $announcement->load('author');
        $categories = Announcement::CATEGORIES;

        return view('announcements.show', compact('announcement', 'categories'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $categories = Announcement::CATEGORIES;

        return view('announcements.edit', compact('announcement', 'categories'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $validated = $this->validated($request);
        // Edits must not demote published/archived records via the checkbox.
        // Status changes go through publish/unpublish routes with their own policy.
        unset($validated['status']);
        unset($validated['published_at']);

        DB::transaction(function () use ($validated, $request, $announcement) {
            $before = $this->auditSnapshot($announcement);
            $announcement->update($validated);

            if ($this->shouldPublish($request) && ($announcement->isDraft() || $announcement->isArchived())) {
                $this->authorize('publish', $announcement);
                $announcement->update([
                    'status' => Announcement::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]);
            }

            $this->auditUpdated($announcement, $before, "Updated announcement \"{$announcement->title}\"");
        });

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function publish(Announcement $announcement)
    {
        $this->authorize('publish', $announcement);

        $before = $this->auditSnapshot($announcement);
        $announcement->update([
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $this->auditUpdated($announcement, $before, "Published announcement \"{$announcement->title}\"", 'published');

        return back()->with('success', 'Announcement published.');
    }

    public function unpublish(Announcement $announcement)
    {
        $this->authorize('unpublish', $announcement);

        $before = $this->auditSnapshot($announcement);
        $announcement->update(['status' => Announcement::STATUS_ARCHIVED]);
        $this->auditUpdated($announcement, $before, "Archived announcement \"{$announcement->title}\"", 'archived');

        return back()->with('success', 'Announcement archived.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);

        $title = $announcement->title;
        $before = $this->auditSnapshot($announcement);
        try {
            $announcement->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->with('error', 'Cannot delete announcement due to linked records.');
        }
        $this->auditDeleted($announcement, $before, "Deleted announcement \"{$title}\"");

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:10000',
            'category' => 'required|in:'.implode(',', array_keys(Announcement::CATEGORIES)),
        ]);

        $data['status'] = $this->shouldPublish($request)
            ? Announcement::STATUS_PUBLISHED
            : Announcement::STATUS_DRAFT;

        return $data;
    }

    private function shouldPublish(Request $request): bool
    {
        // Matches AnnouncementPolicy::publish: only admin/secretary may publish,
        // and publishing applies to drafts and archived records (republish).
        return $request->boolean('action_publish')
            && $request->user()->hasAnyRole(User::CORE_EDITORS);
    }
}
