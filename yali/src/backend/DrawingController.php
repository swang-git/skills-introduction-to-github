<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class DrawingController extends Controller
{
    private string $disk = 'public';
    // private string $drawingsPath = 'drawings';
    private string $drawingsPath = '/Users/swang/webdata/pics/yali/thumbnails';
    private string $thumbnailsPath = 'thumbnails';
    
    /**
     * Scan filesystem and paginate results
     */
    // public function index(Request $request)
    public function index($page, $perPage)
    {
        // $perPage = min($request->input('per_page', 60), 120);
        // $perPage = min($request->input('per_page', $per_page), 40);
        // $page = $request->input('page', $page);
        
        // Get all drawing files
        $allFiles = $this->getDrawingFiles();
        $total = count($allFiles);
        
        // Manual slice for pagination
        $offset = ($page - 1) * $perPage;
        $pageFiles = array_slice($allFiles, $offset, $perPage);
        
        // Build response items
        $items = array_map(function ($file) {
            return [
                'id' => md5($file),           // stable ID from path
                // 'name' => pathinfo($file, PATHINFO_FILENAME),
                'fnm' => basename($file),
                // 'thumbnail_url' => $this->getThumbnailUrl($file),
                // 'full_url' => Storage::disk($this->disk)->url($file),
                // 'full_url' => url($file),
                // 'size' => Storage::disk($this->disk)->size($file),
                'fsz' => filesize($file),
                // 'modified' => Storage::disk($this->disk)->lastModified($file),
                'dtm' => date('Y.n.j H:i', filemtime($file)),
                'whr' => ['width' => getimagesize($file)[0], 'height' => getimagesize($file)[1]],
                // rat => $width / $height;
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
            'status' => "OK"
        ]);
    }
    
    /**
     * Get all drawing files, sorted
     */
    private function getDrawingFiles(): array
    {
        // $files = Storage::disk($this->disk)->allFiles($this->drawingsPath);
        // Log::log("-CK-files", $files);
        
        // Filter to images only
        $picdir = "/Users/swang/sites/webdata/pics/yali";
        // if ($isIM) $picdir = "/sites/webdata/pics/yaliIM";
		$thumbnails = [];
		$thumbnaildir = "$picdir/thumbnails";
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
    private function getThumbnailUrl(string $drawingPath): string
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