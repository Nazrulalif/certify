<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            width: 297mm;
            /* A4 landscape width */
            height: 210mm;
            /* A4 landscape height */
            position: relative;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
        }

        .field {
            position: absolute;
            overflow: hidden;
            word-wrap: break-word;
        }

        .qr-code {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
        }

        .certificate-number {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 10px;
            color: #666;
        }

        /* Text alignment classes */
        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Font weight */
        .font-bold {
            font-weight: bold;
        }

        .font-italic {
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <!-- Background Image -->
        @if ($backgroundExists ?? false)
            <img src="{{ $backgroundPath }}" alt="Certificate Background" class="background">
        @endif

        <!-- Content Layer -->
        <div class="content">
            <!-- Dynamic Fields -->
            @foreach ($fields as $field)
                <div class="field text-{{ $field['alignment'] }} {{ $field['bold'] ? 'font-bold' : '' }} {{ $field['italic'] ? 'font-italic' : '' }}"
                    style="
                        left: {{ $field['x'] }}px;
                        top: {{ $field['y'] }}px;
                        width: {{ $field['width'] }}px;
                        height: {{ $field['height'] }}px;
                        font-size: {{ $field['font_size'] }}px;
                        font-family: {{ $field['font_family'] }}, 'DejaVu Sans', sans-serif;
                        color: {{ $field['color'] }};
                        line-height: {{ $field['height'] }}px;
                    ">
                    {{ $field['value'] }}
                </div>
            @endforeach

            <!-- QR Code -->
            @if (file_exists($qrCodePath))
                @if (str_ends_with($qrCodePath, '.svg'))
                    <div class="qr-code">
                        {!! file_get_contents($qrCodePath) !!}
                    </div>
                @else
                    <img src="{{ $qrCodePath }}" alt="QR Code" class="qr-code">
                @endif
            @endif

            <!-- Certificate Number -->
            <div class="certificate-number">
                Certificate No: {{ $certificate->certificate_number }}
            </div>
        </div>
    </div>
</body>

</html>
