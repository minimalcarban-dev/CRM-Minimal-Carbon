@php
    $success = session('success');
    $error = session('error') ?? session('fail');
    $warning = session('warning');
    $errorMessages = '';

    if (($errors ?? null)?->any()) {
        $errorMessages = '<ul>';
        foreach (($errors ?? null)->all() as $message) {
            $errorMessages .= '<li>' . e($message) . '</li>';
        }
        $errorMessages .= '</ul>';
    }
@endphp

@if ($success || $error || $warning || ($errors ?? null)?->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($success)
                showToast(@json($success), 4000, 'success');
            @elseif ($warning)
                showToast(@json($warning), 5000, 'warning');
            @elseif ($error)
                showToast(@json($error), 5000, 'error');
            @elseif (($errors ?? null)?->any())
                showToast(@json($errorMessages), 6000, 'error');
            @endif
        });
    </script>
@endif
