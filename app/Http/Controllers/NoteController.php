<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;


class NoteController extends Controller
{
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(
            Note::where('user_id', $user->id)->get()
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $note = Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $user->id,
        ]);

        return response()->json($note, 201);
    }

    public function show($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = Note::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$note) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($note);
    }

    // Tambahan method edit (untuk web UI jika dibutuhkan)
    public function edit($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = Note::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$note) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($note); // atau bisa return view() jika pakai Blade
    }

    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = Note::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$note) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $note->update($request->only(['title', 'content']));

        return response()->json([
            'message' => 'Note updated successfully',
            'note' => $note,
        ]);
    }

    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $note = Note::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$note) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }

   public function adminIndex()
{
    $notes = \App\Models\Note::with('user:id,name,email')->get();
    return response()->json($notes);
}

public function export()
{
    $notes = \App\Models\Note::with('user:id,name')->get();
    $csv = "Title,Content,User\n";

    foreach ($notes as $note) {
        $csv .= "\"{$note->title}\",\"{$note->content}\",\"{$note->user->name}\"\n";
    }

    return response($csv, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename=\"notes.csv\"',
    ]);
}

public function superDestroy($id)
{
    $note = \App\Models\Note::find($id);
    if (!$note) {
        return response()->json(['error' => 'Catatan tidak ditemukan'], 404);
    }

    $note->delete();

    return response()->json(['message' => 'Catatan berhasil dihapus oleh superadmin']);
}

public function adminShow($id)
{
    $note = \App\Models\Note::with('user')->find($id);
    if (!$note) {
        return response()->json(['error' => 'Catatan tidak ditemukan'], 404);
    }
    return response()->json($note);
}


}
