[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$CandidateJson,
    [string]$IndexPath = ''
)

function Normalize-Text([string]$Value) {
    if ([string]::IsNullOrWhiteSpace($Value)) { return '' }
    $decomposed = $Value.Normalize([Text.NormalizationForm]::FormD)
    $plain = [regex]::Replace($decomposed, '\p{Mn}', '')
    return [regex]::Replace($plain.ToLowerInvariant(), '[^a-z0-9]+', ' ').Trim()
}

function Normalize-Phone([string]$Value) {
    if ([string]::IsNullOrWhiteSpace($Value)) { return '' }
    return [regex]::Replace($Value, '\D', '')
}

if ([string]::IsNullOrWhiteSpace($IndexPath)) { $IndexPath = Join-Path $PSScriptRoot 'business-index.jsonl' }
$candidate = Get-Content -Encoding UTF8 -LiteralPath $CandidateJson -Raw | ConvertFrom-Json
$existing = Get-Content -Encoding UTF8 -LiteralPath $IndexPath | Where-Object { -not [string]::IsNullOrWhiteSpace($_) } | ForEach-Object { $_ | ConvertFrom-Json }
$candidateId = [string]$candidate.maps_id
$candidateName = Normalize-Text ([string]$candidate.business_name)
$candidatePhone = Normalize-Phone ([string]$candidate.phone_e164)
$candidateAddress = Normalize-Text ([string]$candidate.address_key)
$matches = foreach ($record in $existing) {
    $recordName = Normalize-Text ([string]$record.business_name)
    $recordPhone = Normalize-Phone ([string]$record.phone_e164)
    $recordAddress = Normalize-Text ([string]$record.address_key)
    $kind = $null
    if ($candidateId -and $record.maps_id -and $candidateId -eq [string]$record.maps_id) { $kind = 'exact_maps_id' }
    elseif ($candidateName -and $candidatePhone -and $candidateName -eq $recordName -and $candidatePhone -eq $recordPhone) { $kind = 'exact_name_and_phone' }
    elseif ($candidateName -and $candidateAddress -and $candidateName -eq $recordName -and $candidateAddress -eq $recordAddress) { $kind = 'exact_name_and_address' }
    elseif ($candidatePhone -and $candidatePhone -eq $recordPhone) { $kind = 'possible_phone_reuse_review_identity' }
    elseif ($candidateName -and $candidateName -eq $recordName) { $kind = 'weak_name_only_review' }
    if ($kind) {
        [pscustomobject]@{
            match_type = $kind
            business_name = $record.business_name
            maps_id = $record.maps_id
            phone_e164 = $record.phone_e164
            address_key = $record.address_key
            prior_status = $record.prior_status
            reevaluate_under_current_rules = $record.reevaluate_under_current_rules
            source_path = $record.source_path
        }
    }
}
$result = if ($matches) { [pscustomobject]@{ decision = 'existing_or_review_before_research'; matches = @($matches) } } else { [pscustomobject]@{ decision = 'no_index_match'; matches = @() } }
$result | ConvertTo-Json -Depth 6



