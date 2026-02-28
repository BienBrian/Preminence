<?php

namespace App\Http\Controllers\Dashboard\FileManager;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\MediaFile;
use App\Models\MediaFolder;
use App\Traits\CompressesImages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileManagerController extends DashboardController
{
    use CompressesImages;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $folders = MediaFolder::whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get();
        return view('dashboard.file-manager.index', compact('folders'));
    }

    public function getFolders(Request $request)
    {
        $parentId = $request->parent_id ?: null;
        $folders = MediaFolder::where('parent_id', $parentId)->orderBy('sort_order')->orderBy('name')->get();
        return response()->json($folders);
    }

    public function getFiles(Request $request)
    {
        $files = MediaFile::where('folder_id', $request->folder_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                $file->url = asset($file->path);
                $file->size_formatted = $this->formatBytes($file->size);
                $file->is_image = str_starts_with($file->mime_type ?? '', 'image/');
                return $file;
            });
        return response()->json($files);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|in:general,event,gallery',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $folder = MediaFolder::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'parent_id' => $request->parent_id ?: null,
            'type' => $request->type ?? 'general',
            'description' => $request->description,
            'show_on_frontend' => $request->has('show_on_frontend') ? 1 : 0,
        ]);

        return response()->json(['success' => 'Folder created', 'folder' => $folder]);
    }

    public function updateFolder(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:media_folders,id',
            'name' => 'required|string|max:100',
        ]);

        $folder = MediaFolder::findOrFail($request->id);
        $folder->update([
            'name' => $request->name,
            'description' => $request->description,
            'show_on_frontend' => $request->has('show_on_frontend') ? 1 : 0,
            'type' => $request->type ?? $folder->type,
        ]);

        return response()->json(['success' => 'Folder updated']);
    }

    public function deleteFolder(Request $request)
    {
        $folder = MediaFolder::findOrFail($request->id);

        // Delete all files in this folder from disk
        foreach ($folder->files as $file) {
            $filePath = public_path($file->path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete the folder directory
        $folderPath = public_path('media/' . $folder->id);
        if (is_dir($folderPath)) {
            rmdir($folderPath);
        }

        $folder->delete();

        return response()->json(['success' => 'Folder deleted']);
    }

    public function uploadFiles(Request $request)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'mp3', 'mp4', 'zip'];
        $request->validate([
            'folder_id' => 'required|exists:media_folders,id',
            'files.*' => 'required|file|max:10240|mimes:' . implode(',', $allowedExtensions),
        ]);

        $folder = MediaFolder::findOrFail($request->folder_id);
        $uploadPath = public_path('media/' . $folder->id);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }
            $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $mimeType = $file->getMimeType();

            // Compress images using trait
            if (str_starts_with($mimeType, 'image/') && in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                $this->compressAndSave($file, $uploadPath, $filename);
            } else {
                $file->move($uploadPath, $filename);
            }

            $mediaFile = MediaFile::create([
                'folder_id' => $folder->id,
                'filename' => $filename,
                'original_name' => $originalName,
                'path' => 'media/' . $folder->id . '/' . $filename,
                'mime_type' => $mimeType,
                'size' => file_exists($uploadPath . '/' . $filename) ? filesize($uploadPath . '/' . $filename) : 0,
                'uploaded_by' => \Auth::id(),
            ]);

            $mediaFile->url = asset($mediaFile->path);
            $mediaFile->size_formatted = $this->formatBytes($mediaFile->size);
            $mediaFile->is_image = str_starts_with($mimeType, 'image/');
            $uploaded[] = $mediaFile;
        }

        return response()->json(['success' => count($uploaded) . ' file(s) uploaded', 'files' => $uploaded]);
    }

    public function deleteFile(Request $request)
    {
        $file = MediaFile::findOrFail($request->id);
        $filePath = public_path($file->path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $file->delete();

        return response()->json(['success' => 'File deleted']);
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
