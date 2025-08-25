# Teacher Authentication API Test Script (PowerShell)
# Tests all authenticated endpoints with both header-based and JSON body authentication

# Configuration
$baseUrl = "http://localhost/amt/api/teacher"
$testCredentials = @{
    email = "mahalakshmisalla70@gmail.com"
    password = "testpass123"
}

# Global variables
$authToken = $null
$userId = $null
$testResults = @()

# Function to make HTTP requests
function Invoke-ApiRequest {
    param(
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [string]$Body = $null
    )
    
    try {
        $response = Invoke-RestMethod -Uri $Url -Method $Method -Headers $Headers -Body $Body -ContentType "application/json"
        return @{
            Success = $true
            StatusCode = 200
            Data = $response
            Error = $null
        }
    }
    catch {
        return @{
            Success = $false
            StatusCode = $_.Exception.Response.StatusCode.value__
            Data = $null
            Error = $_.Exception.Message
        }
    }
}

# Function to test endpoints
function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Method = "GET",
        [string]$AuthMethod = "header",
        [hashtable]$AdditionalData = @{}
    )
    
    Write-Host "Testing: $Name ($AuthMethod authentication)" -ForegroundColor Yellow
    Write-Host "URL: $Url"
    Write-Host "Method: $Method"
    
    $headers = @{
        "Client-Service" = "smartschool"
        "Auth-Key" = "schoolAdmin@"
        "Content-Type" = "application/json"
    }
    
    $body = $null
    
    if ($AuthMethod -eq "header") {
        # Header-based authentication
        $headers["User-ID"] = $userId
        $headers["Authorization"] = $authToken
        
        if ($AdditionalData.Count -gt 0) {
            $body = $AdditionalData | ConvertTo-Json
        }
    }
    else {
        # JSON body authentication
        $authData = @{
            user_id = $userId
            token = $authToken
        }
        $combinedData = $authData + $AdditionalData
        $body = $combinedData | ConvertTo-Json
    }
    
    $result = Invoke-ApiRequest -Url $Url -Method $Method -Headers $headers -Body $body
    
    $testResult = @{
        Name = "$Name`_$AuthMethod"
        Url = $Url
        Method = $Method
        AuthMethod = $AuthMethod
        Success = $result.Success
        StatusCode = $result.StatusCode
        Data = $result.Data
        Error = $result.Error
    }
    
    $script:testResults += $testResult
    
    Write-Host "Status Code: $($result.StatusCode)" -ForegroundColor $(if ($result.Success) { "Green" } else { "Red" })
    
    if ($result.Error) {
        Write-Host "Error: $($result.Error)" -ForegroundColor Red
    }
    
    if ($result.Data) {
        Write-Host "Response: $($result.Data | ConvertTo-Json -Depth 3)" -ForegroundColor Cyan
    }
    
    Write-Host ("-" * 80)
    Write-Host ""
    
    return $result
}

# Function to login
function Invoke-Login {
    Write-Host "=== LOGGING IN ===" -ForegroundColor Magenta
    
    $loginData = $testCredentials | ConvertTo-Json
    $headers = @{
        "Client-Service" = "smartschool"
        "Auth-Key" = "schoolAdmin@"
        "Content-Type" = "application/json"
    }
    
    $result = Invoke-ApiRequest -Url "$baseUrl/login" -Method "POST" -Headers $headers -Body $loginData
    
    Write-Host "Login Status Code: $($result.StatusCode)"
    Write-Host "Login Response: $($result.Data | ConvertTo-Json -Depth 3)"
    
    if ($result.Success -and $result.Data.data.token -and $result.Data.data.users_id) {
        $script:authToken = $result.Data.data.token
        $script:userId = $result.Data.data.users_id
        Write-Host "Login successful! Token: $authToken, User ID: $userId" -ForegroundColor Green
        return $true
    }
    
    Write-Host "Login failed!" -ForegroundColor Red
    return $false
}

# Function to run all tests
function Invoke-AllTests {
    Write-Host "=== TESTING AUTHENTICATED ENDPOINTS ===" -ForegroundColor Magenta
    Write-Host ""
    
    # Test 1: Profile endpoint (GET - header auth)
    Test-Endpoint -Name "Profile" -Url "$baseUrl/profile" -Method "GET" -AuthMethod "header"
    
    # Test 2: Profile endpoint (GET - JSON body auth)
    Test-Endpoint -Name "Profile" -Url "$baseUrl/profile" -Method "GET" -AuthMethod "json_body"
    
    # Test 3: Staff details by ID (GET - header auth)
    Test-Endpoint -Name "Staff Details" -Url "$baseUrl/staff/1" -Method "GET" -AuthMethod "header"
    
    # Test 4: Staff search (GET - header auth)
    Test-Endpoint -Name "Staff Search" -Url "$baseUrl/staff-search?search=teacher&limit=5" -Method "GET" -AuthMethod "header"
    
    # Test 5: Staff by role (GET - header auth)
    Test-Endpoint -Name "Staff By Role" -Url "$baseUrl/staff-by-role/1" -Method "GET" -AuthMethod "header"
    
    # Test 6: Staff by employee ID (GET - header auth)
    Test-Endpoint -Name "Staff By Employee ID" -Url "$baseUrl/staff-by-employee-id/EMP001" -Method "GET" -AuthMethod "header"
    
    # Test 7: Logout (POST - JSON body auth)
    Test-Endpoint -Name "Logout" -Url "$baseUrl/logout" -Method "POST" -AuthMethod "json_body" -AdditionalData @{ deviceToken = "test_device_token" }
}

# Function to generate test report
function Show-TestReport {
    Write-Host ""
    Write-Host "=== TEST REPORT ===" -ForegroundColor Magenta
    
    $totalTests = $testResults.Count
    $passedTests = ($testResults | Where-Object { $_.Success }).Count
    
    foreach ($result in $testResults) {
        $status = if ($result.Success) { "PASS" } else { "FAIL" }
        $color = if ($result.Success) { "Green" } else { "Red" }
        
        Write-Host ("{0,-40}: {1} (HTTP {2})" -f $result.Name, $status, $result.StatusCode) -ForegroundColor $color
    }
    
    Write-Host ""
    Write-Host "Summary: $passedTests/$totalTests tests passed" -ForegroundColor $(if ($passedTests -eq $totalTests) { "Green" } else { "Yellow" })
    
    if ($passedTests -lt $totalTests) {
        Write-Host ""
        Write-Host "Failed tests details:" -ForegroundColor Red
        $failedTests = $testResults | Where-Object { -not $_.Success }
        foreach ($failed in $failedTests) {
            Write-Host "- $($failed.Name): HTTP $($failed.StatusCode)" -ForegroundColor Red
            if ($failed.Error) {
                Write-Host "  Error: $($failed.Error)" -ForegroundColor Red
            }
        }
    }
}

# Main execution
Write-Host "Teacher Authentication API Test Script" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

if (Invoke-Login) {
    Invoke-AllTests
    Show-TestReport
}
else {
    Write-Host "Cannot proceed with tests - login failed!" -ForegroundColor Red
}

Write-Host ""
Write-Host "Test completed." -ForegroundColor Cyan
