$taskName = "MoodTrailDailyReminder"
$phpPath = "C:\xampp\php\php.exe"
$scriptPath = "C:\xampp\htdocs\moodtracker\cron\send-reminders.php"

$action = New-ScheduledTaskAction -Execute $phpPath -Argument $scriptPath
$trigger = New-ScheduledTaskTrigger -Daily -At "09:00" -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 1)
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

try {
    Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Force
    Write-Host "Scheduled task '$taskName' created successfully." -ForegroundColor Green
    Write-Host "It will run every minute to check for pending mood reminders." -ForegroundColor Cyan
} catch {
    Write-Host "Failed to create task: $_" -ForegroundColor Red
    Write-Host "Try running PowerShell as Administrator." -ForegroundColor Yellow
}
