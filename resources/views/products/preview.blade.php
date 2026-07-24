<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Preview - {{ $product->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body, html {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #0f172a;
        }
        .preview-topbar {
            height: 54px;
            background-color: #0f172a;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            color: #f1f5f9;
            z-index: 1000;
            position: relative;
        }
        .preview-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #ffffff;
            text-decoration: none;
        }
        .preview-logo svg {
            width: 24px;
            height: 24px;
        }
        .preview-info-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .preview-title {
            font-size: 0.95rem;
            font-weight: 500;
            color: #cbd5e1;
        }
        .preview-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 36px;
            padding: 0 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            border: 1px solid transparent;
            line-height: 1;
        }
        .preview-btn i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 0.85rem;
            margin-top: 0;
        }
        .preview-btn span {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }
        .preview-btn-primary {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        .preview-btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .preview-btn-secondary {
            background-color: transparent;
            color: #94a3b8;
            border-color: #334155;
        }
        .preview-btn-secondary:hover {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        .preview-iframe-container {
            width: 100%;
            height: calc(100% - 54px);
            position: relative;
        }
        .preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #ffffff;
        }
    </style>
</head>
<body>
    <div class="preview-topbar">
        <a href="{{ url('/') }}" class="preview-logo">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 21.35L10.55 20.03C5.4 15.36 2 12.28 2 8.5C2 5.42 4.42 3 7.5 3C9.24 3 10.91 3.81 12 5.09C13.09 3.81 14.76 3 16.5 3C19.58 3 22 5.42 22 8.5C22 12.28 18.6 15.36 13.45 20.04L12 21.35Z" fill="#ef4444"/>
            </svg>
            <span>HeadRandom</span>
        </a>
        
        <div class="preview-info-actions">
            <span class="preview-title">{{ $product->name }}</span>
            
            <a href="{{ $product->frontend_url }}#packages" class="preview-btn preview-btn-primary">
                <i class="fas fa-shopping-cart"></i>
                <span>Buy Now</span>
            </a>
            
            <a href="{{ $product->frontend_url }}" class="preview-btn preview-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Project</span>
            </a>
        </div>
    </div>
    
    <div class="preview-iframe-container">
        <iframe src="{{ $demoUrl }}" class="preview-iframe" allow="geolocation; microphone; camera; midi; vr; accelerometer; gyroscope; payment; ambient-light-sensor; encrypted-media"></iframe>
    </div>
</body>
</html>
