$folders = @(
    "app\Features\ReferenceData",
    "app\Features\ProjectAnalysis"
)

foreach ($folder in $folders) {
    Get-ChildItem $folder -Recurse -File -Include *.php |
    ForEach-Object {
        Write-Output "`n========== $($_.FullName) =========="
        Get-Content $_.FullName
    }
}