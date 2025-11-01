<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ $event->name }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .registration-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .registration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .card-body-custom {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 14px 40px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .required-asterisk {
            color: #e74c3c;
        }

        .event-description {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header-custom">
                <h1 class="h3 mb-2">{{ $event->name }}</h1>
                @if ($event->description)
                    <p class="mb-0 opacity-90">{{ $event->description }}</p>
                @endif
            </div>

            <div class="card-body-custom">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('register.store', $event->slug) }}" method="POST">
                    @csrf

                    @foreach ($event->fields as $field)
                        <div class="mb-4">
                            <label for="{{ $field->field_name }}" class="form-label">
                                {{ $field->field_label }}
                                @if ($field->required)
                                    <span class="required-asterisk">*</span>
                                @endif
                            </label>

                            @if ($field->field_type === 'text')
                                <input type="text"
                                    class="form-control @error($field->field_name) is-invalid @enderror"
                                    id="{{ $field->field_name }}" name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name) }}" {{ $field->required ? 'required' : '' }}>
                            @elseif ($field->field_type === 'email')
                                <input type="email"
                                    class="form-control @error($field->field_name) is-invalid @enderror"
                                    id="{{ $field->field_name }}" name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name) }}" {{ $field->required ? 'required' : '' }}>
                            @elseif ($field->field_type === 'number')
                                <input type="number"
                                    class="form-control @error($field->field_name) is-invalid @enderror"
                                    id="{{ $field->field_name }}" name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name) }}" {{ $field->required ? 'required' : '' }}>
                            @elseif ($field->field_type === 'date')
                                <input type="date"
                                    class="form-control @error($field->field_name) is-invalid @enderror"
                                    id="{{ $field->field_name }}" name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name) }}" {{ $field->required ? 'required' : '' }}>
                            @elseif ($field->field_type === 'textarea')
                                <textarea class="form-control @error($field->field_name) is-invalid @enderror" id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}" rows="4" {{ $field->required ? 'required' : '' }}>{{ old($field->field_name) }}</textarea>
                            @elseif ($field->field_type === 'select' && is_array($field->options))
                                <select class="form-select @error($field->field_name) is-invalid @enderror"
                                    id="{{ $field->field_name }}" name="{{ $field->field_name }}"
                                    {{ $field->required ? 'required' : '' }}>
                                    <option value="">Select an option</option>
                                    @foreach ($field->options as $option)
                                        <option value="{{ $option }}"
                                            {{ old($field->field_name) == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error($field->field_name)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <div class="mt-5">
                        <button type="submit" class="btn btn-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                class="bi bi-check-circle me-2" viewBox="0 0 16 16"
                                style="display: inline-block; vertical-align: middle;">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                <path
                                    d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z" />
                            </svg>
                            Submit Registration
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <span class="required-asterisk">*</span> Required fields
                        </small>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-white mb-0">
                <small>&copy; {{ date('Y') }} Certificate Generator. All rights reserved.</small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
