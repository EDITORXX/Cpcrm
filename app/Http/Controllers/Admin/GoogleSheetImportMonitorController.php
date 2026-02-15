<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetImportLog;
use App\Models\GoogleSheetsConfig;

class GoogleSheetImportMonitorController extends Controller
{
    public function index()
    {
        $configs = GoogleSheetsConfig::with([
            'creator:id,name',
            'importState',
            'importLogs' => function ($query) {
                $query->latest('started_at')->limit(1);
            },
        ])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        $logs = GoogleSheetImportLog::with([
            'config:id,sheet_name,created_by',
            'config.creator:id,name',
        ])
            ->latest('started_at')
            ->limit(100)
            ->get();

        return view('integrations.google-sheet-import-monitor', [
            'configs' => $configs,
            'logs' => $logs,
        ]);
    }
}

