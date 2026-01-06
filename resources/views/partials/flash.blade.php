@php
    $success = session('success');
    $error = session('error') ?? session('fail');
    $warning = session('warning');
@endphp

@if ($success || $error || $warning || ($errors ?? null)?->any())
    @php
        if ($success) {
            $type = 'success';
            $title = 'Success!';
            $icon = 'bi-check-circle-fill';
        } elseif ($warning) {
            $type = 'warning';
            $title = 'Warning!';
            $icon = 'bi-exclamation-triangle-fill';
        } else {
            $type = 'danger';
            $title = 'Error!';
            $icon = 'bi-x-circle-fill';
        }
    @endphp
    <div class="alert-card {{ $type }} mb-4">
        <div class="alert-icon">
            <i class="bi {{ $icon }}"></i>
        </div>
        <div class="alert-content">
            <h5 class="alert-title">{{ $title }}</h5>
            @if($success)
                <p class="alert-message">{!! $success !!}</p>
            @elseif($warning)
                <div class="alert-message">{!! $warning !!}</div>
            @elseif($error)
                <p class="alert-message">{{ $error }}</p>
            @elseif(($errors ?? null)?->any())
                <ul class="alert-message" style="margin:0; padding-left:1.25rem;">
                    @foreach(($errors ?? null)->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif