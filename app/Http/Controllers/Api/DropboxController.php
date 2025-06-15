<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dropbox;

class DropboxController extends Controller
{
    /**
     * Mengembalikan daftar semua dropbox yang aktif.
     */
    public function index()
    {
        $dropboxes = Dropbox::where('status', 'active')->get();

        return response()->json($dropboxes);
    }
}
