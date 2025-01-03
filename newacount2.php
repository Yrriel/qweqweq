<?php 

session_start();

if(empty($_SESSION['email'])){
    header('location:login.php');
    die();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Account Setup</title>
    <style>
        body {
            background: radial-gradient(rgb(248, 192, 255), rgb(128, 62, 128), black);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            height: 100vh;
            width: 100vw;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            color: #fff;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
            background-color: #221D38;
        }
        .container.active {
            display: block;
        }
        h1, h2 {
            text-align: center;
            color: #eee6ff;
        }
        p {
            text-align: center;
        }
        .serial-number{
            text-align: center;
            background-color: #6a4191;
            font-size:2em;
            font:bold;
            border-radius: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group input, .form-group button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            background: #6146ff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 20px;
        }
        .form-group button:disabled {
            background: #aaa;
            cursor: not-allowed;
        }
        .form-group button:hover:enabled {
            background: #4424f8;
        }
        .fingerprint-animation {
            text-align: center;
            margin: 20px 0;
        }
        .fingerprint-animation img {
            width: 150px;
            height: 150px;
            animation: pulse 1.5s infinite ease-in-out;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.1);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 0.7;
            }
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Step 1: Serial Number -->
    <div class="container active" id="page-1">
        <h1>Step 1: Connect Your ESP32</h1>
        <p>Please use the following serial number to connect your ESP32 device:</p>
        <div class="serial-number" id="serial-number">Generating...</div>
        <p id="status-text">Status: Waiting for connection...</p>
        <div class="form-group">
            <button onclick="nextPage(2)" id="next-btn-1" disabled>Next</button>
        </div>
    </div>

    <!-- Step 2: Fingerprint Enrollment -->
<div class="container" id="page-2">
    <h1>Step 2: Enroll Fingerprint</h1>
    <p>Enter the fingerprint index to be used (e.g., 1, 2, 3):</p>
    <div class="form-group">
        <input type="number" id="fingerprint-index" placeholder="Enter fingerprint index">
        <button onclick="saveFingerprintIndex()" id="save-fingerprint-btn">Save Fingerprint Index</button>
    </div>
    <p>Place your finger on the fingerprint sensor when prompted.</p>
    <div class="fingerprint-animation">
        <img src="src\svg\fingerprint.svg" alt="Fingerprint Animation">
    </div>
    <p id="step-2-status">Status: Waiting for input...</p>
    <div class="form-group">
        <button onclick="pollFingerprintEnrollment()" id="start-enrollment-btn">Start Enrollment</button>
    </div>
    <div class="form-group">
        <button id="next-btn-2" disabled onclick="nextPage(3)">Next</button>
    </div>
</div>
    <!-- Step 3: User Details -->
    <div class="container" id="page-3">
        <h1>Step 3: Personal Information</h1>
        <form id="user-details-form">
            <div class="form-group">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" placeholder="Enter your first name">
            </div>
            <div class="form-group">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" placeholder="Enter your last name">
            </div>
            <div class="form-group">
                <button onclick="updateUserFullName(event)" type="submit">Finish</button>
            </div>
            <p id="step-3-status"></p>
        </form>
    </div>

    <script>
        let serialNumber = '';

        // Generate serial number and check ESP32 connection
        async function generateSerialNumber() {
            const serialNumberElement = document.getElementById('serial-number');

            try {
                const response = await fetch('generate_serial.php');
                const data = await response.json();
                serialNumber = data.serialNumber;
                serialNumberElement.textContent = serialNumber;
                console.log('serialNumner : ' + serialNumber);
                checkESP32Connection();
            } catch (error) {
                console.error('Error generating serial number:', error);
                serialNumberElement.textContent = 'Error generating serial number.';
            }
        }

        // Check ESP32 connection
        async function checkESP32Connection() {
            console.log('checkESP32Connection called');

            const statusText = document.getElementById('status-text');
            const nextBtn1 = document.getElementById('next-btn-1');

            const interval = setInterval(async () => {
                try {
                    console.log('tried fetching to check_32.php');
                    const response = await fetch(`check_esp32.php?serialNumber=${serialNumber}`);
                    const data = await response.json();

                    if (data.status === 'connected') {
                        statusText.textContent = "Status: ESP32 connected successfully!";
                        nextBtn1.disabled = false;
                        clearInterval(interval);
                    }
                } catch (error) {
                    console.error('Error checking ESP32 connection:', error);
                }
            }, 3000);
        }

    // async function updateUserNameOnDB() {
    //     const firstName = document.getElementById('first-name').value.trim();
    //     const lastName = document.getElementById('last-name').value.trim();
    //     try {
    //         const response = await fetch('update_nameonFingerprintDB.php', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/x-www-form-urlencoded',
    //             },
    //             body: `firstName=${firstName}&lastName=${lastName}&serialNumber=${serialNumber}`
    //         });

    //         const data = await response.json();

    //         if (data.status === 'success') {
    //             console.log("Initialized Name");
    //         } else {
    //             console.log("Failed to Initialized Name");
    //             console.log(data);
    //         }
    //     } catch (error) {
    //         console.error('Error saving fingerprint index:', error);
    //     }
    // }

    async function updateUserFullName(event) {
    event.preventDefault(event);
    const firstName = document.getElementById('first-name').value.trim();
    const lastName = document.getElementById('last-name').value.trim();
    const statusText = document.getElementById('step-3-status');

    // Ensure both fields are filled
    if (!firstName || !lastName) {
        alert('Please fill in both first and last name.');
        return;
    }

    // Create the data to send
    const postData = new URLSearchParams({
        firstName: firstName,
        lastName: lastName,
        serialNumber: serialNumber
    });  

    // Perform the fetch POST request
    fetch('updateUserCredentials.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: postData.toString() // Convert the data to URL-encoded string
    })
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            // Handle the response
            if (data.status === 'success') {
                console.log("success!");
                initializeDashboardInfo();
                statusText.textContent = 'User full name updated successfully!.';
             
            } else {
                alert(`Error: ${data.message || 'Failed to update user full name.'}`);
            }
        })
        .catch(error => {
            // Handle network errors
            console.error('Error updating user full name:', error);
            statusText.textContent = 'There was a connection problem updating the user full name. Please try again.';

        });
    }

        async function initializeDashboardInfo() {
            try {
            const response = await fetch('dashboardinformation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ESP32SerialNumber=${serialNumber}`
            });

            const data = await response.json();

            if (data.status === 'success') {
                console.log("Initialized Dashboard information");
                console.log("redirecting to dashboard");
                delayRedirect("backendlogout.php", 3000);
            } else {
                console.log("Failed to Initialized Dashboard information");
                console.log(data);
            }
        } catch (error) {
            console.error('Error saving fingerprint index:', error);
        }


        }

        function delayRedirect(url, delay) {
        setTimeout(function () {
        window.location.href = url;
        }, delay);
}
        async function saveFingerprintIndex() {
        const fingerprintIndexbutton = document.getElementById('save-fingerprint-btn');
        const fingerprintIndex = document.getElementById('fingerprint-index').value;
        const statusText = document.getElementById('step-2-status');

        // Validate the fingerprint index
        if (isNaN(fingerprintIndex) || fingerprintIndex <= 0) {
            alert("Please enter a valid fingerprint index.");
            return;
        }

        // Send the fingerprint index to the server to be saved in the database
        try {
            const response = await fetch('save_fingerprint_index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `fingerprintIndex=${fingerprintIndex}`
            });

            const data = await response.json();

            if (data.status === 'success') {
                statusText.textContent = 'Status: Fingerprint index saved successfully!';
                // Enable the "Start Enrollment" button
                document.getElementById('start-enrollment-btn').disabled = false;
                fingerprintIndex.disabled = true;
                fingerprintIndexbutton.disabled = true;
            } else {
                statusText.textContent = 'Status: Error saving fingerprint index.';
            }
        } catch (error) {
            console.error('Error saving fingerprint index:', error);
            statusText.textContent = 'Status: Error saving fingerprint index.';
        }
    }

    async function doorlockReadMode(){
        const updateResponse = await fetch('update_option.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `option=3`
        });

        const updateData = await updateResponse.json();
        console.log('Update Data:', updateData);  // Log the response data

        if (updateData.status !== 'success') {
            console.error('Error enabling readmode fingerpint:', updateData);
            return;
        }
    }

async function clearStatus(){
    const statusText = document.getElementById('step-2-status');
    try {
        statusText.textContent = "Status: Preparing to enable fingerprint scanning...";
        const updateResponse = await fetch('send_log.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `status=waiting`
        });

        const updateData = await updateResponse.json();
        console.log('Update Data:', updateData);  // Log the response data

        if (updateData.status !== 'success') {
            console.error('Error enabling fingerprint scanning:', updateData);
            statusText.textContent = "Status: Error clearing fingerprint scanning. Please try again.";
            return;
        }

    } catch (error) {
        console.error('Error during fingerprint scanning setup:', error);
        statusText.textContent = "Status: Error clearing fingerprint scanning. Please try again.";
    }
}
       
async function addusertoFingerprintDatabase() {
    const fingerprintIndex = document.getElementById('fingerprint-index').value;
    try {
        console.log("initializing...");
        const updateResponse = await fetch('send_fingerprinttoDB.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `ESP32SerialNumber=${serialNumber}&indexFingerprint=${fingerprintIndex}`

        });

        const updateData = await updateResponse.json();
        console.log('insert Data:', updateData);  // Log the response data

        if (updateData.status !== 'success') {
            console.error('Error initializing', updateData);
            return;
        }
        console.log("--------------DONE adding---------...");
    } catch (error) {
        console.error('Error during Error initializing scanning setup:', error);
        statusText.textContent = "Status: Error enabling Error initializing scanning. Please try again.";
    }
}

async function pollFingerprintEnrollment() {
    const fingerprintIndex = document.getElementById('fingerprint-index').value;
    const statusText = document.getElementById('step-2-status');
    const nextBtn2 = document.getElementById('next-btn-2');
    

    try {
        statusText.textContent = "Status: Preparing to enable fingerprint scanning...";
        const updateResponse = await fetch('update_option.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `option=1`
        });

        const updateData = await updateResponse.json();
        console.log('Update Data:', updateData);  // Log the response data

        if (updateData.status !== 'success') {
            console.error('Error enabling fingerprint scanning:', updateData);
            statusText.textContent = "Status: Error enabling fingerprint scanning. Please try again.";
            return;
        }

        console.log('------------------------Polling starting...');
        // Start polling after enabling fingerprint scanning
            // resetOption();
            pollStatus();

    } catch (error) {
        console.error('Error during fingerprint scanning setup:', error);
        statusText.textContent = "Status: Error enabling fingerprint scanning. Please try again.";
    }
}

async function pollStatus() {
    const statusText = document.getElementById('step-2-status');

    try {
        const response = await fetch('send_log.php');
        const data = await response.json();
        console.log('Polling Data:', data); // Log the polling data

        if (data.status === 'waiting') {
            statusText.textContent = "Status: Waiting for user to place a finger...";
        } else if (data.status === 'waiting2'){
            statusText.textContent = "Status: Place same finger again";
        } else if (data.status === 'Fingerprint-converted'){
            statusText.textContent = "Status: Lift your finger";
        } else if (data.status === 'scanning') {
            statusText.textContent = "Status: Scanning fingerprint...";
        } else if (data.status === 'scan2failed') {
            statusText.textContent = "Status: Image conversion failed (second scan)";
        } else if (data.status === 'Fingerprintmismatch') {
            statusText.textContent = "Status: Fingerprint mismatch";
        } else if (data.status === 'success') {
            statusText.textContent = "Status: Enrollment successful!";
            $runnin = true;
            
            addusertoFingerprintDatabase();
            doorlockReadMode();
            document.getElementById('next-btn-2').disabled = false; // Enable Next button
        } else if (data.status === 'error') {
            statusText.textContent = `Status: Error - ${data.message}`;
        }

        // If the enrollment is successful or an error occurs, stop polling
        if (data.status === 'success' || data.status === 'error') {
            return; // Stop polling
        }

        // Continue polling every 3 seconds if still in the 'waiting' or 'scanning' state
        setTimeout(pollStatus, 200);

        } catch (error) {
            console.error('Error polling fingerprint status:', error);
            statusText.textContent = "Status: Error during fingerprint polling. Please try again.";
        }
}


        // Navigate between steps
        function nextPage(pageNumber) {
            document.querySelectorAll('.container').forEach(container => {
                container.classList.remove('active');
            });
            document.getElementById(`page-${pageNumber}`).classList.add('active');
        }

        // Initialize on page load
        window.onload = function(){
            clearStatus();
            generateSerialNumber();
            
        }
    </script>
</body>
</html>
