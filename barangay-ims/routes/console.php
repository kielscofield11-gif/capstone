<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:generate')->dailyAt('07:00')->withoutOverlapping();
