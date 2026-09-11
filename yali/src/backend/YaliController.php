<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
// use Illuminate\Support\Facades\File;


class YaliController extends Controller
{
    // private string $disk = 'public';
    // private string $drawingsPath = 'drawings';
    // private string $drawingsPath = '/Users/swang/sites/webdata/pics/yali/thumbnails';
    // private string $thumbnailsPath = 'thumbnails';

     /**
     * get rondam pic
     */
    public function getRandomPic() { Log::info("-fn-getRandomPic");
        // Get all drawing files
        $allFiles = $this->getAllPicFullFileNames();
        // $total = count($allFiles);
        // $rfile = $allFiles[array_rand($allFiles)];
        $rfile = Arr::random($allFiles);
        $rat = getimagesize($rfile)[0] / getimagesize($rfile)[1];
        $rfile = "/pics/yali/". basename($rfile);
        Log::info("random file: $rfile");
        return ['randomFile' => $rfile, 'rat' => $rat, 'status' => "OK"];
    }

    /**
     * Scan filesystem and paginate results and filtered by year
     */
    public function getPixByYM($ym) { Log::info("-fn-getPixByYM [$ym]");
        // Get all drawing files
        $allFiles = $this->getAllPicFullFileNames();
        $total = count($allFiles);
        $files = [];
        forEach($allFiles as $file) {
            $imgFile = str_replace('/thumbnails', '', $file);
            $ymx = date('Y.m', filemtime($file));
            if ($ymx == $ym) {
                $data = [
                    'fnm' => basename($file), 
                    'fsz' => filesize($imgFile), 
                    'dtm' => date('Y.m.j H:i', filemtime($file)), 
                    'whr' => ['width' => getimagesize($imgFile)[0], 'height' => getimagesize($imgFile)[1]]
                ];
                $files[] = $data;
            }
        }
        return response()->json([
            'data' => $files,
            'total' => count($files),
            'ym' => $ym,
            'status' => "OK"
        ]);
    }
    /**
     * Scan filesystem and paginate results
     */
    public function getPages($page, $perPage) { Log::info("-fn-getPages page=$page, perPage=$perPage");
        // Get all drawing files
        $allFiles = $this->getAllPicFullFileNames();
        $total = count($allFiles);
        $years = [];
        $yms = [];
        forEach($allFiles as $file) {
            $year = date('Y', filemtime($file));
            $ym = date('Y.m', filemtime($file));
            $years[] = $year;
            $yms[] = $ym;
        }
        sort($years, SORT_NATURAL);
        sort($yms, SORT_NATURAL);

        // Manual slice for pagination
        $offset = ($page - 1) * $perPage;
        $pageFiles = array_slice($allFiles, $offset, $perPage);

        $pdiff = $perPage - count($pageFiles);
        if ($pdiff > 0) {
            $addingFiles = array_slice($allFiles, 0, $pdiff);
            $pageFiles = array_merge($pageFiles, $addingFiles);
        }
        
        // Build response items
        $items = array_map(function ($file) {
            $imgFile = str_replace('/thumbnails', '', $file);
            return [
                'id' => md5($file),           // stable ID from path
                'fsz' => filesize($imgFile),
                'fnm' => basename($file),
                'dtm' => date('Y.m.j H:i', filemtime($file)),
                'whr' => ['width' => getimagesize($imgFile)[0], 'height' => getimagesize($imgFile)[1]],
            ];
        }, $pageFiles);
        
        // Create paginator manually
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            // ['path' => $request->url()]
        );
        
        return response()->json([
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $perPage,
            'has_more' => $paginator->hasMorePages(),
            'years' => array_values(array_unique($years)),
            'yms' => array_values(array_unique($yms)),
            'status' => "OK"
        ]);
    }

    /**
     * move dulicated file to dup_files and dump_files/thumbnails
     */
    public function removeDupFile($dupFile) { Log::info("remove duplicate file[$dupFile]\n");
        // $picsDir = '/Users/swang/sites/webdata/pics';
        $picsDir = config('constants.PICS_DIR');
        $yaliDir = "$picsDir/yali";
        $dupFilesDir = "$picsDir/dup_files";
        $thumbnailsDir = "$yaliDir/thumbnails";
        $mvDupCode = rename("$yaliDir/$dupFile", "$dupFilesDir/$dupFile");
        $mvThmCode = rename("$thumbnailsDir/$dupFile", "$dupFilesDir/thumbnails/$dupFile");
        Log::info("rename dup code=$mvDupCode");
        Log::info("rename thm code=$mvThmCode");
        if ($mvDupCode == True and $mvThmCode == True) return ['status' => "OK"];
        return ['status' => "FAILED"];
    }
    /**
     * reverse the "move dulicated file to dup_files and dump_files/thumbnails"
     */
    public function undoRemovedDupFile($dupFile) { Log::info("undo removed duplicate file[$dupFile]\n");
        // $picsDir = '/Users/swang/sites/webdata/pics';
        $picsDir = config('constants.PICS_DIR');
        $yaliDir = "$picsDir/yali";
        $dupFilesDir = "$picsDir/dup_files";
        $thumbnailsDir = "$yaliDir/thumbnails";
        $removedThumbnail = "$dupFilesDir/thumbnails/$dupFile";
        $removedFile = "$dupFilesDir/$dupFile";
        if (!(file_exists($removedThumbnail) and file_exists($removedFile) and is_file($removedThumbnail) and is_file($removedFile))) {
            Log::info("No $removedThumbnail or No $removedFile or not file(s)");
            return ['status' => $removedFile];
        }
        $undoDupCode = rename("$removedFile", "$yaliDir/$dupFile");
        $undoThmCode = rename("$removedThumbnail", "$thumbnailsDir/$dupFile");
        Log::info("undo remove dup code=$undoDupCode");
        Log::info("undo remove thm code=$undoThmCode");
        if ($undoDupCode == True and $undoThmCode == True) return ['status' => "OK"];
        return ['status' => "FAILED"];
    }
    /**
     * Get all filenames (full path like: /Users/swang/sites/webdata/pics/yali/ya20260811.jpg), sorted
     */
    private function getAllPicFullFileNames(): array
    {
        // $files = Storage::disk($this->disk)->allFiles($this->drawingsPath);
        // Log::log("-CK-files", $files);
        
        // Filter to images only
        // $picdir = "/Users/swang/sites/webdata/pics/yali";
        $yaliDir = config('constants.PICS_DIR') . '/yali';
        // if ($isIM) $picdir = "/sites/webdata/pics/yaliIM";
		$thumbnails = [];
		$thumbnaildir = "$yaliDir/thumbnails";
        $files = glob("$thumbnaildir/*.{jpg,webp,jpeg,png,gif,JPG,JPEG,PNG,GIG}", GLOB_BRACE);
        // $files = array_filter($files, function ($file) {
        //     $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        //     return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        // });
        
        // Sort by filename (numeric prefix: 001, 002, etc.)
        // sort($files, SORT_NATURAL);

        usort($files, function($a, $b) use ($thumbnaildir) {
            return filemtime($b) - filemtime($a);
        });
        
        // Re-index array
        // return array_values($files);
        return $files;
        // return ['status' => "OK", 'da' => $thumbnails];
    }
    
    /**
     * Check if thumbnail exists, return appropriate URL
     */
    private function XXXgetThumbnailUrl(string $drawingPath): string
    {
        $filename = basename($drawingPath);
        $thumbPath = $this->thumbnailsPath . '/thumb_' . $filename;
        
        if (Storage::disk($this->disk)->exists($thumbPath)) {
            return Storage::disk($this->disk)->url($thumbPath);
        }
        
        // Fallback: return full image (browser will scale it down)
        return Storage::disk($this->disk)->url($drawingPath);
    }
}