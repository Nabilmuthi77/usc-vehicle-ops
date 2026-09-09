<?php

namespace App\Http\Controllers;

use App\Enums\VehicleDocumentType;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleDocumentController extends Controller
{
    public function __construct(
        private readonly FileUploadService $uploads
    ) {}

    public function create(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.documents.create', [
            'vehicle' => $vehicle,
            'documentTypes' => VehicleDocumentType::options(),
        ]);
    }

    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'document_type' => ['required', 'string'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:90'],
            'notes' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $this->uploads->store($request->file('file'), 'documents');
        }

        $vehicle->documents()->create($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('status', 'Dokumen kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle, VehicleDocument $document): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.documents.edit', [
            'vehicle' => $vehicle,
            'document' => $document,
            'documentTypes' => VehicleDocumentType::options(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle, VehicleDocument $document): RedirectResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'document_type' => ['required', 'string'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:90'],
            'notes' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('file')) {
            $newPath = $this->uploads->store($request->file('file'), 'documents');
            $this->uploads->delete($document->file_path);
            $validated['file_path'] = $newPath;
        }

        $document->update($validated);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('status', 'Dokumen kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle, VehicleDocument $document): RedirectResponse
    {
        $this->authorize('update', $vehicle);
        
        // We use soft deletes, so the file remains.
        $document->delete();

        return redirect()->route('vehicles.show', $vehicle)
            ->with('status', 'Dokumen kendaraan berhasil dihapus.');
    }
}
