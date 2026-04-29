<?php

return [
    'name' => 'PayPal',
    'description' => 'Thanh toán an toàn với PayPal hoặc Thẻ Tín dụng/Ghi nợ',
    'button' => [
        'pay_with' => 'Thanh toán với PayPal',
    ],
    'messages' => [
        'processing' => 'Đang xử lý thanh toán...',
        'success' => 'Thanh toán thành công!',
        'failed' => 'Thanh toán thất bại. Vui lòng thử lại.',
        'cancelled' => 'Thanh toán đã bị hủy.',
    ],
    'admin' => [
        'settings' => 'Cài đặt PayPal',
        'mode' => 'Chế độ',
        'sandbox' => 'Sandbox (Thử nghiệm)',
        'live' => 'Live (Sản phẩm)',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'webhook_id' => 'Webhook ID',
        'test_connection' => 'Kiểm tra kết nối',
        'connection_success' => 'Kết nối thành công!',
        'connection_failed' => 'Kết nối thất bại. Vui lòng kiểm tra thông tin đăng nhập.',
    ],
];
