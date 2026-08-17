<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CompressBeneficiaryPhotos extends Command
{
    protected $signature = 'photos:compress {maxSizeKB=300}';
    protected $description = 'Compress all beneficiary photos to under specified size (default 300KB)';

    public function handle()
    {
        $maxSizeKB = (int) $this->argument('maxSizeKB');
        $this->info("Compressing beneficiary photos to under {$maxSizeKB}KB...");

        $files = Storage::disk('public')->files('beneficiary_photos');
        $totalFiles = count($files);
        $compressedCount = 0;
        $totalSaved = 0;

        $this->output->progressStart($totalFiles);

        foreach ($files as $file) {
            $fullPath = Storage::disk('public')->path($file);
            
            if (!file_exists($fullPath)) {
                $this->output->progressAdvance();
                continue;
            }

            $originalSize = filesize($fullPath) / 1024; // KB

            if ($originalSize <= $maxSizeKB) {
                $this->output->progressAdvance();
                continue;
            }

            try {
                $imageInfo = getimagesize($fullPath);
                if (!$imageInfo) {
                    $this->output->progressAdvance();
                    continue;
                }

                $mime = $imageInfo['mime'];
                $compressedData = null;
                $sizeKB = $originalSize;

                // Start with quality 75
                $quality = 75;
                $minQuality = 5;

                do {
                    if ($mime == 'image/jpeg') {
                        $gdImage = imagecreatefromjpeg($fullPath);
                        ob_start();
                        imagejpeg($gdImage, null, $quality);
                        $compressedData = ob_get_clean();
                        imagedestroy($gdImage);
                    } elseif ($mime == 'image/png') {
                        $gdImage = imagecreatefrompng($fullPath);
                        ob_start();
                        imagepng($gdImage, null, (int)(9 * $quality / 100));
                        $compressedData = ob_get_clean();
                        imagedestroy($gdImage);
                    } else {
                        break;
                    }
                    
                    $sizeKB = strlen($compressedData) / 1024;
                    
                    if ($sizeKB <= $maxSizeKB) {
                        break;
                    }
                    
                    $quality -= 5;
                } while ($quality >= $minQuality);

                // If still too large, resize dimensions
                if ($sizeKB > $maxSizeKB) {
                    if ($mime == 'image/jpeg') {
                        $srcImage = imagecreatefromjpeg($fullPath);
                    } elseif ($mime == 'image/png') {
                        $srcImage = imagecreatefrompng($fullPath);
                    } else {
                        $this->output->progressAdvance();
                        continue;
                    }
                    
                    $width = imagesx($srcImage);
                    $height = imagesy($srcImage);
                    $scale = 0.8;
                    
                    do {
                        $newWidth = $width * $scale;
                        $newHeight = $height * $scale;
                        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                        
                        // Handle transparency for PNG
                        if ($mime == 'image/png') {
                            imagealphablending($dstImage, false);
                            imagesavealpha($dstImage, true);
                            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                        }
                        
                        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        
                        ob_start();
                        if ($mime == 'image/jpeg') {
                            imagejpeg($dstImage, null, 70);
                        } else {
                            imagepng($dstImage, null, 6);
                        }
                        $compressedData = ob_get_clean();
                        imagedestroy($dstImage);
                        
                        $sizeKB = strlen($compressedData) / 1024;
                        $scale -= 0.1;
                    } while ($sizeKB > $maxSizeKB && $scale > 0.2);
                    
                    imagedestroy($srcImage);
                }

                if ($compressedData && $sizeKB < $originalSize) {
                    // Backup original
                    $backupPath = $fullPath . '.backup';
                    copy($fullPath, $backupPath);
                    
                    // Save compressed
                    file_put_contents($fullPath, $compressedData);
                    
                    $newSize = filesize($fullPath) / 1024;
                    $saved = $originalSize - $newSize;
                    $totalSaved += $saved;
                    $compressedCount++;
                    
                    // Remove backup if successful
                    unlink($backupPath);
                }
            } catch (\Exception $e) {
                $this->warn("Failed to compress {$file}: " . $e->getMessage());
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Compression complete!");
        $this->info("Compressed: {$compressedCount} / {$totalFiles} files");
        $this->info("Total space saved: " . number_format($totalSaved / 1024, 2) . " MB");

        return 0;
    }
}
