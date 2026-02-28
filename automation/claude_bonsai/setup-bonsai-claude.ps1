param(
  [Parameter(Mandatory = $false)]
  [string]$Token,

  [switch]$DryRun,
  [switch]$NoPrompt,
  [switch]$Backup
)

$ErrorActionPreference = "Stop"

function Read-TokenFromPrompt {
  if ($NoPrompt) {
    throw "Token is required. Pass -Token or omit -NoPrompt."
  }

  $secure = Read-Host "Paste Bonsai API key (starts with sk_cr_)" -AsSecureString
  $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
  try {
    return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
  }
  finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr) | Out-Null
  }
}

function ConvertTo-HashtableDeep {
  param([Parameter(Mandatory = $true)]$Value)

  if ($null -eq $Value) { return $null }

  if ($Value -is [System.Collections.IDictionary]) {
    $out = [ordered]@{}
    foreach ($k in $Value.Keys) {
      $out[$k] = ConvertTo-HashtableDeep -Value $Value[$k]
    }
    return $out
  }

  if ($Value -is [System.Collections.IEnumerable] -and -not ($Value -is [string])) {
    $arr = @()
    foreach ($item in $Value) {
      $arr += (ConvertTo-HashtableDeep -Value $item)
    }
    return ,$arr
  }

  if ($Value -is [pscustomobject]) {
    $out = [ordered]@{}
    foreach ($p in $Value.PSObject.Properties) {
      $out[$p.Name] = ConvertTo-HashtableDeep -Value $p.Value
    }
    return $out
  }

  return $Value
}

function Write-Utf8NoBom {
  param(
    [Parameter(Mandatory = $true)][string]$Path,
    [Parameter(Mandatory = $true)][string]$Content
  )
  $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
  [System.IO.File]::WriteAllText($Path, $Content, $utf8NoBom)
}

$claudeDir = Join-Path $env:USERPROFILE ".claude"
$settingsPath = Join-Path $claudeDir "settings.json"

if (-not (Test-Path -LiteralPath $claudeDir)) {
  if ($DryRun) {
    Write-Host "[DryRun] Would create directory: $claudeDir"
  } else {
    New-Item -ItemType Directory -Path $claudeDir | Out-Null
  }
}

$existing = [ordered]@{}
if (Test-Path -LiteralPath $settingsPath) {
  $raw = Get-Content -LiteralPath $settingsPath -Raw
  try {
    $parsed = $raw | ConvertFrom-Json
    $existing = ConvertTo-HashtableDeep -Value $parsed
    if (-not ($existing -is [System.Collections.IDictionary])) {
      throw "settings.json root must be a JSON object."
    }
  } catch {
    throw "Failed to parse existing JSON at $settingsPath. Fix it manually or delete the file and rerun. Error: $($_.Exception.Message)"
  }
}

if ([string]::IsNullOrWhiteSpace($Token)) {
  if ($existing.Contains("env") -and ($existing["env"] -is [System.Collections.IDictionary])) {
    $maybe = $null
    if ($existing["env"].Contains("ANTHROPIC_AUTH_TOKEN")) { $maybe = $existing["env"]["ANTHROPIC_AUTH_TOKEN"] }
    elseif ($existing["env"].Contains("ANTHROPIC_API_KEY")) { $maybe = $existing["env"]["ANTHROPIC_API_KEY"] }

    if ($maybe -is [string] -and -not [string]::IsNullOrWhiteSpace($maybe)) {
      $Token = $maybe
    }
  }
}

if ([string]::IsNullOrWhiteSpace($Token)) {
  $Token = Read-TokenFromPrompt
}

$Token = $Token.Trim()
if ($Token.Length -lt 10) {
  throw "Token looks too short. Aborting."
}
if (-not $Token.StartsWith("sk_cr_")) {
  Write-Warning "Token does not start with 'sk_cr_'. If it's correct for your Bonsai account, you can ignore this warning."
}

if (-not ($existing.Contains("env"))) {
  $existing["env"] = [ordered]@{}
}
if (-not ($existing["env"] -is [System.Collections.IDictionary])) {
  $existing["env"] = ConvertTo-HashtableDeep -Value $existing["env"]
  if (-not ($existing["env"] -is [System.Collections.IDictionary])) {
    throw "Existing 'env' in settings.json is not an object. Aborting to avoid data loss."
  }
}

$existing["env"]["ANTHROPIC_BASE_URL"] = "https://go.trybons.ai"
$existing["env"]["ANTHROPIC_AUTH_TOKEN"] = $Token
$existing["env"]["ANTHROPIC_API_KEY"] = $Token

$json = ($existing | ConvertTo-Json -Depth 50)

if ($Backup -and (Test-Path -LiteralPath $settingsPath)) {
  $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
  $backupPath = "$settingsPath.$stamp.bak"
  if ($DryRun) {
    Write-Host "[DryRun] Would backup: $settingsPath -> $backupPath"
  } else {
    Copy-Item -LiteralPath $settingsPath -Destination $backupPath
  }
}

if ($DryRun) {
  Write-Host "[DryRun] Would write: $settingsPath"
  $redacted = ($existing | ConvertTo-Json -Depth 50)
  $redacted = $redacted `
    -replace '(?m)("ANTHROPIC_AUTH_TOKEN"\s*:\s*)"(?:[^"\\]|\\.)*"', '$1"***REDACTED***"' `
    -replace '(?m)("ANTHROPIC_API_KEY"\s*:\s*)"(?:[^"\\]|\\.)*"', '$1"***REDACTED***"'
  Write-Host $redacted
  exit 0
}

Write-Utf8NoBom -Path $settingsPath -Content ($json + "`n")
Write-Host "OK. Updated: $settingsPath"
Write-Host "Restart Cursor. In Claude Code login prompt choose 'Maybe later' / close it."
