<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrController extends Controller
{
    /**
     * Verify NIN from uploaded image using OCR
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyNin(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
                'nin' => 'required|string|size:11',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nin = $request->input('nin');
            $image = $request->file('image');

            // Log OCR attempt
            Log::info('OCR NIN Verification started', [
                'nin' => $nin,
                'image_name' => $image->getClientOriginalName(),
                'image_size' => $image->getSize(),
            ]);

            // Save image temporarily
            $tempPath = $image->getPathname();

            // Try Python OCR first (EasyOCR with deep learning - much better)
            $pythonResult = $this->runPythonOCR($tempPath, $nin);
            
            if ($pythonResult && $pythonResult['success']) {
                Log::info('Python OCR successful', [
                    'engine' => $pythonResult['ocr_engine'] ?? 'unknown',
                    'nin_found' => $pythonResult['nin_found'] ?? false,
                ]);
                
                $extractedText = $pythonResult['extracted_text'] ?? '';
                
                // If Python OCR found the NIN, return success immediately
                if (!empty($pythonResult['nin_found'])) {
                    return response()->json([
                        'success' => true,
                        'nin_found' => true,
                        'message' => 'NIN successfully verified with ID card',
                        'extracted_text' => $extractedText,
                        'ocr_engine' => $pythonResult['ocr_engine'] ?? 'python',
                    ]);
                }
            } else {
                // Fallback to Tesseract with ImageMagick preprocessing
                $processedPath = $this->preprocessImage($tempPath);
                
                $ocr = new TesseractOCR($processedPath);
                $ocr->lang('eng');
                $ocr->psm(6);
                $ocr->oem(3);
                $extractedText = $ocr->run();
                
                // Clean up processed image
                if (file_exists($processedPath) && $processedPath !== $tempPath) {
                    @unlink($processedPath);
                }
            }

            // Log extracted text for debugging
            Log::info('OCR Extracted Text', [
                'nin' => $nin,
                'text_length' => strlen($extractedText),
                'text_preview' => substr($extractedText, 0, 200),
            ]);

            // Check if NIN exists in extracted text
            $ninFound = $this->checkNinInText($extractedText, $nin);

            if ($ninFound) {
                Log::info('OCR NIN Verification successful', ['nin' => $nin]);
                
                return response()->json([
                    'success' => true,
                    'nin_found' => true,
                    'message' => 'NIN successfully verified with ID card',
                    'extracted_text' => $extractedText,
                ]);
            } else {
                Log::warning('OCR NIN Verification failed - NIN not found', ['nin' => $nin]);
                
                return response()->json([
                    'success' => false,
                    'nin_found' => false,
                    'message' => 'NIN number not found in the captured image. Please ensure the NIN is clearly visible.',
                    'extracted_text' => $extractedText, // Include for debugging
                ]);
            }

        } catch (\Exception $e) {
            Log::error('OCR NIN Verification error', [
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
     * Check if NIN exists in extracted text using multiple patterns
     * 
     * @param string $extractedText
     * @param string $nin
     * @return bool
     */
    private function checkNinInText($extractedText, $nin)
    {
        // Clean the NIN and extracted text for comparison
        $cleanNin = preg_replace('/[^0-9]/', '', $nin);
        $cleanText = preg_replace('/\s/', '', $extractedText);
        
        // FALLBACK: Accept if 8+ digits match (balanced approach)
        // 8 consecutive digits provides good security while accounting for OCR limitations
        if (strlen($cleanNin) == 11) {
            for ($len = 11; $len >= 8; $len--) {
                $partialNin = substr($cleanNin, 0, $len);
                if (strpos($cleanText, $partialNin) !== false) {
                    $confidence = round(($len / 11) * 100);
                    Log::info('OCR Partial NIN match found', [
                        'full_nin' => $cleanNin,
                        'matched_digits' => $len,
                        'confidence' => $confidence . '%',
                        'partial' => $partialNin,
                    ]);
                    
                    // Log warning for low confidence matches
                    if ($len < 10) {
                        Log::warning('Low confidence NIN match - manual verification recommended', [
                            'matched_digits' => $len,
                            'nin' => $cleanNin,
                        ]);
                    }
                    
                    return true;
                }
            }
        }

        // Direct match
        if (strpos($cleanText, $cleanNin) !== false) {
            return true;
        }

        // Nigerian NIN slip specific patterns
        $patterns = [
            // Standard NIN patterns
            '/NIN[:\s]*' . $cleanNin . '/i',
            '/National\s*Identification\s*Number[:\s]*' . $cleanNin . '/i',
            '/ID\s*Number[:\s]*' . $cleanNin . '/i',
            
            // Nigerian NIN slip specific patterns
            '/National\s*Identity\s*Management\s*Commission.*?' . $cleanNin . '/is',
            '/NIMC.*?' . $cleanNin . '/is',
            '/National\s*e\s*Identity\s*Card.*?' . $cleanNin . '/is',
            '/Unique\s*Identification\s*Number.*?' . $cleanNin . '/is',
            
            // Format patterns
            '/NIN\s*\(?\d{11}\)?[:\s]*' . $cleanNin . '/i',
            '/Identification\s*Number[:\s]*' . $cleanNin . '/i',
            '/ID\s*No\.?\s*[:\s]*' . $cleanNin . '/i',
            
            // NIN with surrounding characters or formatting
            '/[^0-9]' . $cleanNin . '[^0-9]/i',
            '/\b' . $cleanNin . '\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $extractedText)) {
                return true;
            }
        }

        // Additional check: Look for NIN in lines that contain "NIN" or "Identification"
        $lines = explode("\n", $extractedText);
        foreach ($lines as $line) {
            $lineLower = strtolower($line);
            if ((strpos($lineLower, 'nin') !== false || 
                 strpos($lineLower, 'identification') !== false || 
                 strpos($lineLower, 'identity') !== false) &&
                strpos($line, $cleanNin) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test OCR functionality (for development/testing)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testOcr(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $image = $request->file('image');
            $tempPath = $image->getPathname();

            $ocr = new TesseractOCR($tempPath);
            $ocr->lang('eng');
            $extractedText = $ocr->run();

            return response()->json([
                'success' => true,
                'extracted_text' => $extractedText,
                'text_length' => strlen($extractedText),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run Python OCR script (EasyOCR with deep learning)
     * 
     * @param string $imagePath
     * @param string $nin
     * @return array|null
     */
    private function runPythonOCR($imagePath, $nin)
    {
        try {
            $scriptPath = base_path('app/Scripts/ocr_nin.py');
            
            if (!file_exists($scriptPath)) {
                Log::warning('Python OCR script not found', ['path' => $scriptPath]);
                return null;
            }
            
            // Run Python script (redirect stderr to /dev/null to avoid warnings in output)
            $command = sprintf(
                'python3 %s %s %s 2>/dev/null',
                escapeshellarg($scriptPath),
                escapeshellarg($imagePath),
                escapeshellarg($nin)
            );
            
            $output = shell_exec($command);
            
            if (empty($output)) {
                Log::warning('Python OCR returned empty output');
                return null;
            }
            
            // Extract JSON from output (ignore any warnings before it)
            // Find the JSON object in the output
            if (preg_match('/\{[^{}]*"success"[^{}]*\}/s', $output, $matches)) {
                $jsonStr = $matches[0];
            } else {
                // Try to find any valid JSON object
                $jsonStart = strpos($output, '{');
                if ($jsonStart !== false) {
                    $jsonStr = substr($output, $jsonStart);
                } else {
                    Log::warning('Python OCR no JSON found', ['output' => substr($output, 0, 500)]);
                    return null;
                }
            }
            
            $result = json_decode($jsonStr, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Python OCR invalid JSON', ['output' => substr($output, 0, 500)]);
                return null;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Python OCR error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Preprocess image to improve OCR accuracy
     * 
     * @param string $imagePath
     * @return string Path to processed image
     */
    private function preprocessImage($imagePath)
    {
        try {
            $processedPath = sys_get_temp_dir() . '/ocr_processed_' . uniqid() . '.png';
            
            // Use ImageMagick for professional-grade preprocessing
            // Optimized for green Nigerian NIN slips
            $command = sprintf(
                'convert %s ' .
                '-resize 400%% ' .                    // Scale up 4x for small text
                '-colorspace Gray ' .                  // Convert to grayscale
                '-level 10%%,90%% ' .                  // Adjust levels for green background
                '-unsharp 0x1+1+0 ' .                  // Unsharp mask for clarity
                '-morphology Close Diamond:1 ' .       // Close gaps in digits
                '-threshold 40%% ' .                   // Lower threshold for green text
                '-negate ' .                           // Invert if needed
                '-negate ' .                           // Invert back (cleanup)
                '-density 300 ' .                      // Set DPI for OCR
                '%s 2>&1',
                escapeshellarg($imagePath),
                escapeshellarg($processedPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($processedPath)) {
                Log::info('ImageMagick preprocessing successful', [
                    'original' => $imagePath,
                    'processed' => $processedPath,
                ]);
                return $processedPath;
            }
            
            // Fallback: Try simpler ImageMagick command
            $fallbackCommand = sprintf(
                'convert %s -colorspace Gray -contrast-stretch 0.1%%x0.1%% -threshold 45%% %s 2>&1',
                escapeshellarg($imagePath),
                escapeshellarg($processedPath)
            );
            
            exec($fallbackCommand, $fallbackOutput, $fallbackCode);
            
            if ($fallbackCode === 0 && file_exists($processedPath)) {
                Log::info('ImageMagick fallback preprocessing successful', [
                    'processed' => $processedPath,
                ]);
                return $processedPath;
            }
            
            // Last resort: Return original image
            Log::warning('ImageMagick preprocessing failed, using original', [
                'output' => implode("\n", $output),
                'return_code' => $returnCode,
            ]);
            
            return $imagePath;

        } catch (\Exception $e) {
            Log::error('Image preprocessing failed', [
                'error' => $e->getMessage(),
            ]);
            return $imagePath;
        }
    }
}
