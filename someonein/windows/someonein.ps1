# Powershell to access the anyonein web page computer sensor
# Run from task scheduler of some kind

$web = New-Object Net.Webclient
$web.DownloadString("http://example.com/anyonein/?sensor=computer&show=none")
