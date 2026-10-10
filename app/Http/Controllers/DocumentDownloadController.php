<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentDownloadController extends Controller
{
    public function __construct(
        protected DocumentStorageService $documentStorageService
    ) {}

    public function download(Request $request, ApplicationDocument $document)
    {
        return $this->documentStorageService->download($document, Auth::user());
    }
}
