<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,emergency,maintenance',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement = Announcement::create([
            ...$request->all(),
            'created_by' => auth()->id(),
        ]);

        return response()->json($announcement, 201);
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement->load('creator'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'type' => 'in:general,emergency,maintenance',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcement->update($request->all());
        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return response()->json(null, 204);
    }

    public function getActive()
    {
        $announcements = Announcement::with('creator')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($announcements);
    }

    public function getByType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:general,emergency,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $announcements = Announcement::with('creator')
            ->where('type', $request->type)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($announcements);
    }

    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => !$announcement->is_active
        ]);
        return response()->json($announcement);
    }
} 