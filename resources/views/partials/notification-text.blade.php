@if (($notification->data['kind'] ?? null) === 'order_status')
{{ __('app.order_status_changed', ['number' => $notification->data['number'], 'status' => __('app.'.$notification->data['status'])]) }}
@elseif (isset($notification->data['number']))
{{ __('app.new_order_number', ['number' => $notification->data['number']]) }}
@else
{{ $notification->data['message'] ?? $notification->data['body'] ?? __('app.new_notification') }}
@endif
