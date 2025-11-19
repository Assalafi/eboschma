<?php

namespace App\Services;

use TCPDF;

class PdfService
{
    public function generateIdCard($beneficiary)
    {
        // Create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('BOSCHMA Healthcare Management System');
        $pdf->SetAuthor('BOSCHMA');
        $pdf->SetTitle('ID Card - ' . $beneficiary->boschma_no);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 15);
        
        // Set image scale factor
        $pdf->setImageScale(1.53);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Configure TCPDF to preserve CSS exactly as written
        $pdf->setCellHeightRatio(1.5);
        
        // Set CSS preservation options
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Generate HTML content
        $html = view('admin.beneficiaries.id-card-pdf-tcpdf', compact('beneficiary'))->render();
        
        // Write HTML content with CSS preservation
        $pdf->writeHTML($html, true, false, true, false, '', true, true, true, true, '');
        
        return $pdf;
    }
}
