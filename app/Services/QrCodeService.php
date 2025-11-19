<?php

namespace App\Services;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Color\Rgb;

class QrCodeService
{
    /**
     * Generate QR code for beneficiary ID card
     *
     * @param array $beneficiaryData
     * @return string Base64 encoded SVG QR code
     */
    public static function generateBeneficiaryQrCode($beneficiaryData)
    {
        // Create the QR code data with beneficiary information
        $qrData = self::formatBeneficiaryData($beneficiaryData);
        
        // Create renderer with proper Color objects and better error correction
        $renderer = new ImageRenderer(
            new RendererStyle(150, 4, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0))),
            new SvgImageBackEnd()
        );
        
        // Create writer
        $writer = new Writer($renderer);
        
        // Generate QR code
        $qrCode = $writer->writeString($qrData);
        
        // Convert to base64
        $base64 = base64_encode($qrCode);
        
        return 'data:image/svg+xml;base64,' . $base64;
    }
    
    /**
     * Format beneficiary data for QR code
     *
     * @param array $beneficiaryData
     * @return string
     */
    private static function formatBeneficiaryData($beneficiaryData)
    {
        // Create human-readable format for local people
        $readableData = "BOSCHMA HEALTHCARE ID CARD\n";
        
        // Primary Beneficiary Information
        $readableData .= "PRIMARY BENEFICIARY:\n";
        $readableData .= "Name: " . ($beneficiaryData['fullname'] ?? 'N/A') . "\n";
        $readableData .= "BOSCHMA ID: " . ($beneficiaryData['boschma_no'] ?? 'N/A') . "\n";
        $readableData .= "DP Number: " . ($beneficiaryData['dp_no'] ?? 'N/A') . "\n";
        $readableData .= "Facility: " . ($beneficiaryData['facility'] ?? 'N/A') . "\n";
        
        // Add spouse information if available
        if (isset($beneficiaryData['spouse']) && $beneficiaryData['spouse']) {
            $readableData .= "\nSPOUSE:\n";
            $readableData .= "Name: " . ($beneficiaryData['spouse']['name'] ?? 'N/A') . "\n";
            $readableData .= "BOSCHMA ID: " . ($beneficiaryData['spouse']['boschma_no'] ?? 'N/A') . "\n";
            $readableData .= "Facility: " . ($beneficiaryData['spouse']['facility'] ?? 'N/A') . "\n";
        }
        
        // Add children information if available
        if (isset($beneficiaryData['children']) && !empty($beneficiaryData['children'])) {
            $readableData .= "\nCHILDREN:\n";
            $childNumber = 1;
            foreach ($beneficiaryData['children'] as $child) {
                $readableData .= "Child " . $childNumber . ":\n";
                $readableData .= "  Name: " . ($child['name'] ?? 'N/A') . "\n";
                $readableData .= "  BOSCHMA ID: " . ($child['boschma_no'] ?? 'N/A') . "\n";
                $readableData .= "  Gender: " . ($child['gender'] ?? 'N/A') . "\n";
                $readableData .= "  Facility: " . ($child['facility'] ?? 'N/A') . "\n";
                $childNumber++;
            }
        }
        
        return $readableData;
    }
}
