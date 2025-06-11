protected function schedule(Schedule $schedule)
{
    $schedule->command('subscriptions:check')->dailyAt('00:00');
}