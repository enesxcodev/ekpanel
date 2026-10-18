param([string]$StatusPath, [string]$ReadyPath, [string]$Mode, [int]$HoldMilliseconds = 700, [string]$ReleasePath)
$ErrorActionPreference = 'Stop'
$sharing = [System.IO.FileShare]::ReadWrite -bor [System.IO.FileShare]::Delete
if ($Mode -eq 'sharing') { $sharing = [System.IO.FileShare]::Read }
$stream = [System.IO.File]::Open($StatusPath, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read, $sharing)
$mapping = $null
$view = $null
try {
    if ($Mode -eq 'mapping') {
        $mapping = [System.IO.MemoryMappedFiles.MemoryMappedFile]::CreateFromFile($stream, [Guid]::NewGuid().ToString(), 0, [System.IO.MemoryMappedFiles.MemoryMappedFileAccess]::Read, [System.IO.HandleInheritability]::None, $true)
        $view = $mapping.CreateViewAccessor(0, 0, [System.IO.MemoryMappedFiles.MemoryMappedFileAccess]::Read)
    }
    [System.IO.File]::WriteAllText($ReadyPath, 'ready')
    if ($ReleasePath) {
        $deadline = [DateTime]::UtcNow.AddSeconds(25)
        while (-not [System.IO.File]::Exists($ReleasePath) -and [DateTime]::UtcNow -lt $deadline) {
            Start-Sleep -Milliseconds 20
        }
    } else {
        Start-Sleep -Milliseconds $HoldMilliseconds
    }
} finally {
    if ($null -ne $view) { $view.Dispose() }
    if ($null -ne $mapping) { $mapping.Dispose() }
    $stream.Dispose()
}
