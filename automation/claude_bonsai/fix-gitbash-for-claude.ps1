param(
  [switch]$PrintPaths
)

$ErrorActionPreference = "Stop"

function Resolve-GitBashPath {
  $candidates = @(
    "C:\Program Files\Git\bin\bash.exe",
    "C:\Program Files\Git\usr\bin\bash.exe",
    "C:\Program Files (x86)\Git\bin\bash.exe",
    "C:\Program Files (x86)\Git\usr\bin\bash.exe"
  )

  foreach ($p in $candidates) {
    if (Test-Path -LiteralPath $p) { return $p }
  }

  $cmd = Get-Command bash.exe -ErrorAction SilentlyContinue
  if ($cmd -and (Test-Path -LiteralPath $cmd.Source)) { return $cmd.Source }

  return $null
}

function Resolve-ClaudeExePath {
  $p = Join-Path $env:LOCALAPPDATA "Microsoft\WinGet\Packages\Anthropic.ClaudeCode_Microsoft.Winget.Source_8wekyb3d8bbwe\claude.exe"
  if (Test-Path -LiteralPath $p) { return $p }
  return $null
}

$bash = Resolve-GitBashPath
if (-not $bash) {
  throw "Git Bash (bash.exe) not found. Install Git for Windows, then rerun."
}

[Environment]::SetEnvironmentVariable("CLAUDE_CODE_GIT_BASH_PATH", $bash, "User")
$env:CLAUDE_CODE_GIT_BASH_PATH = $bash

$claude = Resolve-ClaudeExePath
if (-not $claude) {
  throw "claude.exe not found (expected WinGet install). Reinstall 'Anthropic.ClaudeCode' or update the path resolver."
}

if ($PrintPaths) {
  Write-Host ("CLAUDE_CODE_GIT_BASH_PATH=" + $bash)
  Write-Host ("claude.exe=" + $claude)
}

& $claude --version
Write-Host "OK. Git Bash path configured for Claude Code. Restart Cursor/terminal if needed."

