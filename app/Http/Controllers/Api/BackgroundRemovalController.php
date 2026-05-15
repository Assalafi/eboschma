<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BackgroundRemovalController extends Controller
{
    /**
     * Process passport photo with face detection, cropping, and background removal
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBackground(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $image = $request->file('image');
            
            Log::info('Photo processing started', [
                'image_name' => $image->getClientOriginalName(),
                'image_size' => $image->getSize(),
            ]);

            // Create temporary directory for processing
            $tempDir = sys_get_temp_dir();
            $inputPath = tempnam($tempDir, 'input_') . '.' . $image->getClientOriginalExtension();
            $croppedPath = tempnam($tempDir, 'cropped_') . '.jpg';
            $outputPath = tempnam($tempDir, 'output_') . '.png';

            // Save uploaded image temporarily
            $image->move(dirname($inputPath), basename($inputPath));

            // Step 1: Face detection and cropping
            $faceCommand = sprintf(
                '/usr/local/bin/process_passport_photo.py %s %s 2>&1',
                escapeshellarg($inputPath),
                escapeshellarg($croppedPath)
            );

            Log::info('Executing face detection and cropping', ['command' => $faceCommand]);

            exec($faceCommand, $faceOutput, $faceReturnCode);
            
            // Clean up original input file
            if (file_exists($inputPath)) {
                unlink($inputPath);
            }

            if ($faceReturnCode !== 0 || !file_exists($croppedPath)) {
                $errorMessage = implode("\n", $faceOutput);
                
                // Extract error message from Python script
                if (strpos($errorMessage, 'ERROR:') !== false) {
                    preg_match('/ERROR:\s*(.+)/', $errorMessage, $matches);
                    $errorMessage = $matches[1] ?? $errorMessage;
                }
                
                Log::error('Face detection failed', [
                    'return_code' => $faceReturnCode,
                    'output' => $errorMessage,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_type' => 'face_detection',
                ], 400);
            }

            // Step 2: Background removal
            $bgCommand = sprintf(
                '/usr/local/bin/remove_bg.py %s %s 2>&1',
                escapeshellarg($croppedPath),
                escapeshellarg($outputPath)
            );

            Log::info('Executing background removal', ['command' => $bgCommand]);

            exec($bgCommand, $bgOutput, $bgReturnCode);
            
            // Clean up cropped file
            if (file_exists($croppedPath)) {
                unlink($croppedPath);
            }

            if ($bgReturnCode !== 0 || !file_exists($outputPath)) {
                Log::error('Background removal failed', [
                    'return_code' => $bgReturnCode,
                    'output' => implode("\n", $bgOutput),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove background',
                    'error' => implode("\n", $bgOutput),
                ], 500);
            }

            // Read the processed image
            $imageData = file_get_contents($outputPath);
            $base64Image = base64_encode($imageData);

            // Clean up output file
            unlink($outputPath);

            Log::info('Photo processing complete (face detection + background removal)', [
                'output_size' => strlen($imageData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo processed successfully',
                'image' => 'data:image/png;base64,' . $base64Image,
                'size' => strlen($imageData),
            ]);

        } catch (\Exception $e) {
            Log::error('Background removal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test background removal endpoint
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        try {
            // Check if rembg is installed
            exec('which rembg', $output, $returnCode);
            
            if ($returnCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'rembg is not installed or not in PATH',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Background removal service is ready',
                'rembg_path' => trim(implode("\n", $output)),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
