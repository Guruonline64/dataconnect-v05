<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('dataconnect:post-daily-returns')->dailyAt('00:05');
