#!/usr/bin/env python3
"""
NIN OCR Script - Uses EasyOCR (deep learning) for accurate text extraction
Specifically optimized for Nigerian green NIN slips
"""

import sys
import os

# CRITICAL: Redirect stderr BEFORE any imports to suppress C-level warnings
_original_stderr = sys.stderr
sys.stderr = open(os.devnull, 'w')

import json
import re
import warnings

# Suppress all Python warnings
warnings.filterwarnings('ignore')
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
os.environ['PYTORCH_ENABLE_MPS_FALLBACK'] = '1'
os.environ['NNPACK_DISABLE'] = '1'

try:
    import easyocr
    HAS_EASYOCR = True
except ImportError:
    HAS_EASYOCR = False

try:
    import cv2
    import numpy as np
    HAS_OPENCV = True
except ImportError:
    HAS_OPENCV = False

# Restore stderr for our JSON output only
sys.stderr = _original_stderr

# Initialize reader globally (downloads model on first run)
_reader = None

def get_reader():
    global _reader
    if _reader is None and HAS_EASYOCR:
        _reader = easyocr.Reader(['en'], gpu=False, verbose=False)
    return _reader

def extract_text_easyocr(image_path):
    """Extract text using EasyOCR (deep learning based)"""
    reader = get_reader()
    if reader is None:
        return "", "no_easyocr"
    
    # Use EasyOCR with detail to get confidence scores
    results = reader.readtext(image_path, detail=1)
    
    # Extract all text
    all_text = []
    for (bbox, text, conf) in results:
        all_text.append(text)
    
    full_text = ' '.join(all_text)
    return full_text, "easyocr"

def check_nin_match(extracted_text, nin_to_verify):
    """Check if NIN exists in extracted text with flexible matching"""
    clean_verify = re.sub(r'[^0-9]', '', nin_to_verify)
    clean_extracted = re.sub(r'[^0-9]', '', extracted_text)
    
    # Try exact match first
    if clean_verify in clean_extracted:
        return True, 11
    
    # Try matching from start (first N digits)
    for length in range(10, 7, -1):
        partial = clean_verify[:length]
        if partial in clean_extracted:
            return True, length
    
    # Try matching middle digits (skip first, check middle 9)
    middle = clean_verify[1:10]  # digits 2-10
    if middle in clean_extracted:
        return True, 9
    
    # Try matching last N digits
    for length in range(10, 7, -1):
        partial = clean_verify[-length:]
        if partial in clean_extracted:
            return True, length
    
    # Try matching any consecutive 8+ digits
    for start in range(4):  # Try different starting positions
        for length in range(8, 11):
            if start + length <= 11:
                partial = clean_verify[start:start+length]
                if partial in clean_extracted:
                    return True, length
    
    return False, 0

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'success': False, 'error': 'No image path provided'}))
        sys.exit(1)
    
    image_path = sys.argv[1]
    nin_to_verify = sys.argv[2] if len(sys.argv) > 2 else None
    
    try:
        # Extract text using EasyOCR
        extracted_text, engine = extract_text_easyocr(image_path)
        
        # Check if NIN matches
        nin_found = False
        matched_digits = 0
        
        if nin_to_verify:
            nin_found, matched_digits = check_nin_match(extracted_text, nin_to_verify)
        
        result = {
            'success': True,
            'ocr_engine': engine,
            'extracted_text': extracted_text,
            'nin_found': nin_found,
            'matched_digits': matched_digits
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': str(e)
        }))
        sys.exit(1)

if __name__ == '__main__':
    main()
