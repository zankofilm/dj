<?php
return ['enabled_providers'=>array_filter(explode(',',env('PAYMENT_PROVIDERS',''))),'webhook_rate_limit_per_minute'=>(int)env('PAYMENT_WEBHOOK_RATE_LIMIT',120)];
