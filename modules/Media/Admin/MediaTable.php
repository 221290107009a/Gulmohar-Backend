<?php

namespace Modules\Media\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class MediaTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['action'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('thumbnail', function ($file) {
                return view('media::admin.media.partials.table.thumbnail', compact('file'));
            })
            ->editColumn('filesize', function ($file) {
                return $this->format_size($file->size);
            })
            ->addColumn('action', function ($file) {
                return view('media::admin.media.partials.table.action', compact('file'));
            });
    }

    public function format_size($size) {
        if ($size < 1024) {
            return $size . ' B';
        }
        else {
            $size = $size / 1024;
            $units = ['KB', 'MB', 'GB', 'TB'];
            foreach ($units as $unit) {
                if (round($size, 2) >= 1024) {
                    $size = $size / 1024;
                }
                else {
                    break;
                }
        }
        return round($size, 2) . ' ' . $unit;
        }
    }
}
