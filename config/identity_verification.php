<?php

return [
    'min_ocr_confidence' => (float) env('IDENTITY_MIN_OCR_CONFIDENCE', 0.80),
    'min_name_similarity' => (float) env('IDENTITY_MIN_NAME_SIMILARITY', 0.86),
    'min_image_quality_score' => (float) env('IDENTITY_MIN_IMAGE_QUALITY_SCORE', 0.65),
    'max_upload_kb' => (int) env('IDENTITY_MAX_UPLOAD_KB', 8192),
    'session_expiry_hours' => (int) env('IDENTITY_SESSION_EXPIRY_HOURS', 24),
    'retention_days' => (int) env('IDENTITY_DOCUMENT_RETENTION_DAYS', 90),
    'python_binary' => env('IDENTITY_PYTHON_BINARY', 'python'),
    'opencv_script' => base_path('scripts/process_identity_card.py'),
    'provider' => env('JKM_VERIFICATION_PROVIDER', 'unavailable'),
];
