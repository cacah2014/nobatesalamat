<?php

use Illuminate\Support\Facades\Schedule;

// نمونه: پاک‌سازی خودکار کدهای منقضی OTP هر ۱۰ دقیقه
Schedule::command('model:prune')->everyTenMinutes();
