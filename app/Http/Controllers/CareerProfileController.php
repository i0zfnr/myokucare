<?php

namespace App\Http\Controllers;

use App\Models\Oku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareerProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('career-profile.show', ['oku' => $request->user()->oku]);
    }

    public function save(Request $request)
    {
        $user = $request->user();
        $oku = $user->oku;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('okus')->ignore($oku)],
            'gender' => ['required', Rule::in(['Lelaki', 'Perempuan'])],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'marital_status' => ['required', Rule::in(['Berkahwin', 'Bujang', 'Duda', 'Janda'])],
            'address' => ['required', 'string', 'max:2000'],
            'education_level' => ['required', 'string', 'max:100'],
            'oku_card_number' => ['required', 'string', 'max:50', Rule::unique('okus')->ignore($oku)],
            'oku_category' => ['required', Rule::in(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'])],
            'phone_number' => ['required', 'string', 'max:20'],
            'career_summary' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'availability_status' => ['required', Rule::in(['Mencari Kerja', 'Sudah Bekerja', 'Tidak Tersedia'])],
            'oku_card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        unset($data['oku_card_image'], $data['resume']);
        $data['email'] = $user->email;

        DB::transaction(function () use ($request, $user, &$oku, $data): void {
            if ($oku) {
                $oku->update($data);
            } else {
                $oku = Oku::query()->create($data);
                $user->update(['oku_id' => $oku->id]);
            }

            if ($request->hasFile('oku_card_image')) {
                $this->replacePrivateFile($oku, 'oku_card_image_path', $request->file('oku_card_image')->store("oku-documents/{$oku->id}/card", 'local'));
                $oku->forceFill([
                    'verification_status' => 'Pending',
                    'verification_notes' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                ])->save();
            }

            if ($request->hasFile('resume')) {
                $this->replacePrivateFile($oku, 'resume_path', $request->file('resume')->store("oku-documents/{$oku->id}/resume", 'local'));
            }
        });

        return redirect()->route('career-profile.show')->with('success', 'Profil kerjaya anda berjaya dikemas kini.');
    }

    public function document(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['card', 'resume'], true), 404);
        $oku = $request->user()->oku;
        abort_unless($oku, 404);
        $path = $type === 'card' ? $oku->oku_card_image_path : $oku->resume_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function staffDocument(Request $request, Oku $oku, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['card', 'resume'], true), 404);
        $path = $type === 'card' ? $oku->oku_card_image_path : $oku->resume_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function verify(Request $request, Oku $oku)
    {
        abort_unless($oku->oku_card_image_path, 422, 'Imej Kad OKU belum dimuat naik.');
        $data = $request->validate([
            'verification_status' => ['required', Rule::in(['Verified', 'Rejected'])],
            'verification_notes' => ['nullable', 'required_if:verification_status,Rejected', 'string', 'max:2000'],
        ]);

        $oku->update($data + [
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('success', $data['verification_status'] === 'Verified'
            ? 'Kad OKU berjaya disahkan.'
            : 'Pengesahan Kad OKU ditolak dengan catatan.');
    }

    private function replacePrivateFile(Oku $oku, string $attribute, string $newPath): void
    {
        $oldPath = $oku->getAttribute($attribute);
        $oku->forceFill([$attribute => $newPath])->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }
}
