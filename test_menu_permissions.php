<?php
/**
 * Test Script for Menu Permission Filtering
 * 
 * This script helps verify that the menu API endpoint correctly filters
 * menus and submenus based on staff role permissions.
 * 
 * Usage: Access this file via browser: http://localhost/amt/test_menu_permissions.php
 */

// Database configuration
$db_host = 'localhost';
$db_name = 'school6';
$db_user = 'root';
$db_pass = '';

// API endpoint
$api_url = 'http://localhost/amt/api/teacher/menu';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Permission Testing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #2196F3;
        }
        .staff-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .staff-card {
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .staff-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        .staff-card h3 {
            margin: 0 0 10px 0;
            color: #2196F3;
        }
        .staff-card p {
            margin: 5px 0;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-superadmin {
            background: #f44336;
            color: white;
        }
        .badge-role {
            background: #4CAF50;
            color: white;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .menu-item {
            margin: 10px 0;
            padding: 10px;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
        }
        .submenu-item {
            margin: 5px 0 5px 20px;
            padding: 8px;
            background: #f1f8e9;
            border-left: 3px solid #8bc34a;
            font-size: 14px;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-left: 4px solid #c62828;
            margin: 10px 0;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-left: 4px solid #2e7d32;
            margin: 10px 0;
        }
        pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        .btn:hover {
            background: #1976D2;
        }
        .comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .comparison-box {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .comparison-box h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Menu Permission Testing Tool</h1>
        
        <div class="test-section">
            <h2>📋 Test Instructions</h2>
            <ol>
                <li>Select a staff member from the list below</li>
                <li>Click on their card to test the menu API</li>
                <li>Review the returned menus and submenus</li>
                <li>Verify that only permitted items are shown</li>
                <li>Compare with expected permissions for that role</li>
            </ol>
        </div>

        <h2>👥 Available Staff Members</h2>
        <div id="staffList" class="staff-list">
            <div class="loading">Loading staff members...</div>
        </div>

        <div id="results" style="display: none;">
            <h2>📊 Test Results</h2>
            <div id="resultContent" class="result-container"></div>
        </div>
    </div>

    <script>
        // Load staff members
        async function loadStaff() {
            try {
                const response = await fetch('get_staff_list.php');
                const data = await response.json();
                
                if (data.status === 1) {
                    displayStaff(data.staff);
                } else {
                    document.getElementById('staffList').innerHTML = 
                        '<div class="error">Failed to load staff: ' + data.message + '</div>';
                }
            } catch (error) {
                document.getElementById('staffList').innerHTML = 
                    '<div class="error">Error loading staff: ' + error.message + '</div>';
            }
        }

        function displayStaff(staffList) {
            const container = document.getElementById('staffList');
            
            if (staffList.length === 0) {
                container.innerHTML = '<div class="error">No staff members found</div>';
                return;
            }

            container.innerHTML = staffList.map(staff => `
                <div class="staff-card" onclick="testMenu(${staff.id})">
                    <h3>${staff.name} ${staff.surname || ''}</h3>
                    <p><strong>ID:</strong> ${staff.id}</p>
                    <p><strong>Employee ID:</strong> ${staff.employee_id || 'N/A'}</p>
                    <p><strong>Role:</strong> <span class="badge badge-role">${staff.role_name || 'No Role'}</span></p>
                    ${staff.is_superadmin == 1 ? '<p><span class="badge badge-superadmin">SUPERADMIN</span></p>' : ''}
                </div>
            `).join('');
        }

        async function testMenu(staffId) {
            const resultsDiv = document.getElementById('results');
            const resultContent = document.getElementById('resultContent');
            
            resultsDiv.style.display = 'block';
            resultContent.innerHTML = '<div class="loading">⏳ Testing menu API for staff ID: ' + staffId + '...</div>';
            
            try {
                const response = await fetch('<?php echo $api_url; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ staff_id: staffId })
                });
                
                const data = await response.json();
                
                if (data.status === 1) {
                    displayResults(data);
                } else {
                    resultContent.innerHTML = '<div class="error">API Error: ' + data.message + '</div>';
                }
            } catch (error) {
                resultContent.innerHTML = '<div class="error">Request Error: ' + error.message + '</div>';
            }
        }

        function displayResults(data) {
            const resultContent = document.getElementById('resultContent');
            const menus = data.data.menus || [];
            const staffInfo = data.data.staff_info || {};
            const roleInfo = data.data.role || {};
            
            let html = `
                <div class="success">
                    ✅ API Request Successful
                </div>
                
                <h3>Staff Information</h3>
                <p><strong>Name:</strong> ${staffInfo.full_name || 'N/A'}</p>
                <p><strong>Employee ID:</strong> ${staffInfo.employee_id || 'N/A'}</p>
                <p><strong>Role:</strong> ${roleInfo.name || 'N/A'} ${roleInfo.is_superadmin ? '<span class="badge badge-superadmin">SUPERADMIN</span>' : ''}</p>
                
                <h3>Menu Items (${menus.length} total)</h3>
            `;
            
            if (menus.length === 0) {
                html += '<div class="error">⚠️ No menus returned - staff may have no permissions</div>';
            } else {
                menus.forEach(menu => {
                    const submenus = menu.submenus || [];
                    html += `
                        <div class="menu-item">
                            <strong>📁 ${menu.menu}</strong> (ID: ${menu.id})
                            <br><small>Lang Key: ${menu.lang_key || 'N/A'}</small>
                            ${submenus.length > 0 ? `<br><small>Submenus: ${submenus.length}</small>` : '<br><small>No submenus</small>'}
                        </div>
                    `;
                    
                    submenus.forEach(submenu => {
                        html += `
                            <div class="submenu-item">
                                📄 ${submenu.menu} (ID: ${submenu.id})
                                <br><small>URL: ${submenu.url || 'N/A'}</small>
                            </div>
                        `;
                    });
                });
            }
            
            html += `
                <h3>Raw JSON Response</h3>
                <button class="btn" onclick="copyToClipboard()">📋 Copy JSON</button>
                <pre id="jsonResponse">${JSON.stringify(data, null, 2)}</pre>
            `;
            
            resultContent.innerHTML = html;
        }

        function copyToClipboard() {
            const jsonText = document.getElementById('jsonResponse').textContent;
            navigator.clipboard.writeText(jsonText).then(() => {
                alert('JSON copied to clipboard!');
            });
        }

        // Load staff on page load
        loadStaff();
    </script>
</body>
</html>

