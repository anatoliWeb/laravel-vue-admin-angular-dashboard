<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('test-broadcast', static fn () => true);
