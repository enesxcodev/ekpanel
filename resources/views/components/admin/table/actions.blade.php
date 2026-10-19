@props(['actions' => []])

<div class="d-flex gap-2 justify-content-end">
    @foreach ($actions as $action)
        @php
            $actionClass = $action['class'] ?? match ($action['icon']) {
                'edit' => 'btn-primary',
                'trash' => 'btn-danger',
                'eye' => 'btn-info',
                default => 'btn-secondary',
            };
            $canPerformAction = empty($action['permission'])
                || auth('admin')->user()?->can($action['permission'], $action['model'] ?? null);
        @endphp
        @if ($canPerformAction)
            @switch($action['type'] ?? 'link')
                @case('modal')
                    <button type="button" class="btn btn-icon {{ $actionClass }}"
                        data-bs-toggle="modal" data-bs-target="{{ $action['target'] }}"
                        aria-label="{{ $action['label'] }}" title="{{ $action['label'] }}">
                        <x-admin.icon :name="$action['icon']" />
                    </button>
                    @break
                @case('form')
                    @php
                        $confirmModal = $action['confirmModal'] ?? (! empty($action['confirm']) ? [
                            'title' => $action['confirmTitle'] ?? 'Silme işlemini onayla',
                            'message' => $action['confirm'],
                            'confirmLabel' => $action['confirmLabel'] ?? $action['label'],
                            'confirmClass' => $action['confirmClass'] ?? 'btn-danger',
                        ] : null);
                    @endphp
                    <form method="POST" action="{{ $action['url'] }}"
                        @if ($confirmModal)
                            data-confirm-modal
                            data-confirm-title="{{ $confirmModal['title'] ?? 'İşlemi onayla' }}"
                            data-confirm-message="{{ $confirmModal['message'] ?? '' }}"
                            data-confirm-button="{{ $confirmModal['confirmLabel'] ?? 'Onayla' }}"
                            data-confirm-cancel-button="{{ $confirmModal['cancelLabel'] ?? 'Vazgeç' }}"
                            data-confirm-button-class="{{ $confirmModal['confirmClass'] ?? 'btn-danger' }}"
                        @endif>
                        @csrf
                        @method($action['method'] ?? 'DELETE')
                        <button type="submit" class="btn btn-icon {{ $actionClass }}"
                            aria-label="{{ $action['label'] }}" title="{{ $action['label'] }}">
                            <x-admin.icon :name="$action['icon']" />
                        </button>
                    </form>
                    @break
                @default
                    <a href="{{ $action['url'] }}" class="btn btn-icon {{ $actionClass }}"
                        aria-label="{{ $action['label'] }}" title="{{ $action['label'] }}">
                        <x-admin.icon :name="$action['icon']" />
                    </a>
            @endswitch
        @endif
    @endforeach
</div>
