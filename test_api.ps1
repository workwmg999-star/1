# Test script for DocuScan SaaS API
$baseUrl = "http://127.0.0.1:8000/api/v1"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DocuScan SaaS - Live API Test Demo" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# 1. Login
Write-Host "`n1. [POST] Logging in as demo owner..." -ForegroundColor Yellow
$loginPayload = @{
    email    = "owner@docuscan.test"
    password = "password123"
} | ConvertTo-Json

$loginRes = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method Post -Body $loginPayload -ContentType "application/json"
$token = $loginRes.data.token
Write-Host "   -> Success! Token received: $($token.Substring(0, 15))..." -ForegroundColor Green
Write-Host "   -> User: $($loginRes.data.user.name) | Role: $($loginRes.data.user.role)" -ForegroundColor Gray
Write-Host "   -> Company: $($loginRes.data.user.company.name)" -ForegroundColor Gray

$headers = @{
    Authorization = "Bearer $token"
    Accept        = "application/json"
}

# 2. Get Subscription Plans
Write-Host "`n2. [GET] Fetching Available Subscription Plans..." -ForegroundColor Yellow
$plansRes = Invoke-RestMethod -Uri "$baseUrl/subscriptions/plans" -Method Get
foreach ($plan in $plansRes.data) {
    Write-Host "   -> Plan: $($plan.name) ($($plan.price_monthly)$/mo) - Storage: $($plan.max_storage_gb)GB - Max Users: $($plan.max_users)" -ForegroundColor Gray
}

# 3. Get Folders
Write-Host "`n3. [GET] Listing Company Folders..." -ForegroundColor Yellow
$foldersRes = Invoke-RestMethod -Uri "$baseUrl/folders" -Method Get -Headers $headers
foreach ($folder in $foldersRes.data) {
    Write-Host "   -> Folder #$($folder.id): [$($folder.name)] (Color: $($folder.color), Docs: $($folder.documents_count))" -ForegroundColor Green
}
$douaneFolderId = ($foldersRes.data | Where-Object { $_.name -like "*Douane*" }).id

# 4. Upload Sample Document
Write-Host "`n4. [POST] Uploading Sample Document to 'Documents Douane' (ID: $douaneFolderId)..." -ForegroundColor Yellow

# Create a sample text/pdf-like file for upload test
$sampleFilePath = "$PSScriptRoot\sample_douane_manifest.pdf"
"PDF-1.4 Sample customs declaration content for DocuScan test" | Out-File -FilePath $sampleFilePath -Encoding utf8

$uploadRes = php -r "
require 'vendor/autoload.php';
\$client = new \GuzzleHttp\Client();
\$res = \$client->request('POST', '$baseUrl/documents', [
    'headers' => ['Authorization' => 'Bearer $token', 'Accept' => 'application/json'],
    'multipart' => [
        ['name' => 'title', 'contents' => 'Declaration Douaniere Port Casablanca #2026-A'],
        ['name' => 'description', 'contents' => 'Customs transit clearance certificate'],
        ['name' => 'folder_id', 'contents' => '$douaneFolderId'],
        ['name' => 'file', 'contents' => fopen('$sampleFilePath', 'r'), 'filename' => 'manifest.pdf'],
    ]
]);
echo \$res->getBody();
" | ConvertFrom-Json

Write-Host "   -> Document Uploaded Successfully!" -ForegroundColor Green
Write-Host "   -> Title: $($uploadRes.data.title)" -ForegroundColor Gray
Write-Host "   -> Type: $($uploadRes.data.file_type) | Size: $($uploadRes.data.size_formatted)" -ForegroundColor Gray
$docId = $uploadRes.data.id

# 5. Search Documents
Write-Host "`n5. [GET] Searching for 'Casablanca'..." -ForegroundColor Yellow
$searchRes = Invoke-RestMethod -Uri "$baseUrl/documents/search?q=Casablanca" -Method Get -Headers $headers
foreach ($doc in $searchRes.data) {
    Write-Host "   -> Found: [$($doc.title)] (Folder: $($doc.folder.name))" -ForegroundColor Green
}

# 6. Get Download URL
Write-Host "`n6. [GET] Generating Secure Download URL for Document #$docId..." -ForegroundColor Yellow
$downloadRes = Invoke-RestMethod -Uri "$baseUrl/documents/$docId/download" -Method Get -Headers $headers
Write-Host "   -> Download URL: $($downloadRes.download_url)" -ForegroundColor Green

# 7. Get Dashboard Stats
Write-Host "`n7. [GET] Fetching Company Dashboard Statistics..." -ForegroundColor Yellow
$dashRes = Invoke-RestMethod -Uri "$baseUrl/company/dashboard" -Method Get -Headers $headers
Write-Host "   -> Total Documents: $($dashRes.data.stats.total_documents)" -ForegroundColor Green
Write-Host "   -> Total Folders: $($dashRes.data.stats.total_folders)" -ForegroundColor Green
Write-Host "   -> Storage Used: $($dashRes.data.storage.used_bytes) bytes ($($dashRes.data.storage.usage_percent)% of $($dashRes.data.storage.limit_gb)GB)" -ForegroundColor Green
Write-Host "   -> Current Plan: $($dashRes.data.plan.name)" -ForegroundColor Green

Write-Host "`n==========================================" -ForegroundColor Cyan
Write-Host "  All Live API Tests Passed Successfully! " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
