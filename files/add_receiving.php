<?php
if(session_status() == PHP_SESSION_NONE){session_start();}
if (empty($_SESSION) || !isset($_SESSION['user_id'], $_SESSION['access']) || (isset($_SESSION['signed_in']) && $_SESSION['user_id'] === false)) header('location: ../login.php');

// Database connection configuration (replace with your own credentials)
include("../config/config.php");

// Initialize variables to store form data
$itemID = $qrCode = $receiver = $itemName = $brand = $type = $boxPrice = $cuttingPrice = "";
$dateReceived = date('Y-m-d H:i:s'); // Get current date and time
$qty = 0.0; // Default value for qty, allow decimal input
$referenceNumber = "Add Reference Number"; // Default value for reference number
$status = "Pending"; // Default value for status
$error_message = ""; // Initialize error message
$BuyingPrice = 0;
$TotalPrice = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemID = intval($_POST['item_info']); // Assuming this is the item ID selected from the dropdown
    $qrCode = mysqli_real_escape_string($conn, $_POST['qr_code']);
    $receiver = $_SESSION['user_fullname']; // Use session data for the receiver
    $qty = floatval($_POST['qty']); // Allowing decimal input for both receiving_qty and current_qty
    $BuyingPrice = floatval($_POST['BuyingPrice']);
    $TotalPrice = floatval($_POST['TotalPrice']);

    // Check if the same qr_code exists in the receivingmanagement table
    $checkDuplicateReceivingSQL = "SELECT id FROM receivingmanagement WHERE qr_code = ?";
    $stmtCheckDuplicateReceiving = $conn->prepare($checkDuplicateReceivingSQL);
    $stmtCheckDuplicateReceiving->bind_param("s", $qrCode);
    $stmtCheckDuplicateReceiving->execute();
    $duplicateReceivingResult = $stmtCheckDuplicateReceiving->get_result();

    // Check if the same qr_code exists in the stocks table
    $checkDuplicateStocksSQL = "SELECT id FROM stocks WHERE qr_code = ?";
    $stmtCheckDuplicateStocks = $conn->prepare($checkDuplicateStocksSQL);
    $stmtCheckDuplicateStocks->bind_param("s", $qrCode);
    $stmtCheckDuplicateStocks->execute();
    $duplicateStocksResult = $stmtCheckDuplicateStocks->get_result();

    if ($duplicateReceivingResult->num_rows > 0 || $duplicateStocksResult->num_rows > 0) {
        // Duplicate entry found in either table, display an error message
        $error_message = "Error: This QR code already exists in the database.";
    } else {
        // Retrieve additional item information from the "pricemanagement" table
        $sqlItemInfo = "SELECT item_id, item_name, brand, type, box_price, cutting_price, branch_id FROM pricemanagement WHERE item_id = ?";
        $stmtItemInfo = $conn->prepare($sqlItemInfo);
        $stmtItemInfo->bind_param("i", $itemID); // Bind branch_id from session
        $stmtItemInfo->execute();
        $itemInfoResult = $stmtItemInfo->get_result();

        if ($itemInfoResult->num_rows === 1) {
            $itemInfo = $itemInfoResult->fetch_assoc();
            $itemName = $itemInfo['item_name'];
            $brand = $itemInfo['brand'];
            $type = $itemInfo['type'];
            $boxPrice = floatval($itemInfo['box_price']);
            $cuttingPrice = floatval($itemInfo['cutting_price']);
            $branchID = intval($_SESSION['branch_id']);
        } else {
            // Handle the error (e.g., display an error message)
            $error_message = "Error: Item information not found.";
        }
        
        // Insert the new stock item into the database if no duplicate entry found
        if (empty($error_message)) {
            $sql = "INSERT INTO receivingmanagement (item_id, item_name, brand, type, box_price, cutting_price, branch_id, qr_code, receiver, receiving_qty, current_qty, reference_number,buying_price,total_price, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssddisssdddds", $itemID, $itemName, $brand, $type, $boxPrice, $cuttingPrice, $branchID, $qrCode, $receiver, $qty, $qty, $referenceNumber,$BuyingPrice,$TotalPrice, $status);


            if ($stmt->execute()) {
                // Set the success message in a session variable
                $_SESSION['success_message'] = "Stock Added Successfully!";
                // header("Location: ../receiving_management.php");
                
                // Clear specific form fields, retain others
                $qrCode = "";
                $qty = 0.0;
                $BuyingPrice = 0;
                $TotalPrice = 0;
            } else {
                // Handle the error (e.g., display an error message)
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
            $stmtItemInfo->close();
        }
    }

    $stmtCheckDuplicateReceiving->close();
    $stmtCheckDuplicateStocks->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Receiving Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800&family=Fauna+One&family=Poppins:ital,wght@0,100;0,300;0,400;0,500;0,700;1,100&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../stylesheet_folder/index_style.css">
    <!-- JavaScript Includes -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SELECT2 LIBRARY CDN'S -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div id="page_title" class="col-md-5">
                <h3 class="mt-3">ADD RECEIVING ITEM</h3>
            </div>
            <div id="footer_logout" class="col-md-7">    
                <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<p class="">Welcome, ' . $_SESSION['user_fullname'] . '!</p>';
                        // echo '<p class="">Branch ID: ' . $_SESSION['branch_id'] . '</p>'; 
                        echo '<a href="logout.php" class="btn mr-3" >Logout</a>';
                    } else {
                        echo '<p id="logged_out">Please <a href="login.php" class="btn" >log in</a></p>';
                    }
                ?>   
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <a id="back_button" class="btn btn-secondary" href="../receiving_management.php">&larr; Back to Receiving Management</a>
            </div>
        </div>
        <!-- Display the success message here -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div style="color: green;"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?> <!-- Clear the success message session variable -->
        <?php endif; ?>

        
        <?php if (!empty($error_message)) : ?>
            <p style="color: red;"><?= $error_message ?></p>
        <?php endif; ?>

        <!-- Display the QR code form -->
        
        <form id="receivingForm" method="post">
            <!-- QR Scanner start here -->
            <div class="qr_container">
                <div id="qr_display" class="row">
                    <script src="html5-qrcode.min.js"></script>
                    <style>
                    .result{
                        background-color: green;
                        color:#fff;
                        padding:20px;
                    }
                    .row{
                        display:flex;
                    }
                    </style>
                    <div class="row mt-5">
                    <div class="mb-3">
                        <div id="reader"></div>
                    </div>
                    <!--<div class="col" style="padding:30px;">
                        <h4>SCAN RESULT</h4>
                        <div id="result">Result Here</div>
                    </div>-->
                    </div>
                    <div class="row">
                        
                    </div>
                    <script type="text/javascript">
                    /* function onScanSuccess(qrCodeMessage) {
                        document.getElementById('qr_code').value = qrCodeMessage;
                        //document.getElementById('result').innerHTML = '<span class="result">'+qrCodeMessage+'</span>';
                    }
                    function onScanError(errorMessage) {
                    //handle scan error
                    }
                    var html5QrcodeScanner = new Html5QrcodeScanner(
                        "reader", { fps: 10, qrbox: 250 });
                    html5QrcodeScanner.render(onScanSuccess, onScanError); */
                    const html5QrCode = new Html5Qrcode("reader");
                    function qrCodeSuccessCallback (decodedText, decodedResult) {
                        /* handle success */
                        var result = decodedText;
                        document.getElementById('qr_code').value = result;
                    };
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                    // If you want to prefer front camera
                    //html5QrCode.start({ facingMode: "user" }, config, qrCodeSuccessCallback);

                    // If you want to prefer back camera
                    html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);

                    // Select front camera or fail with `OverconstrainedError`.
                    //html5QrCode.start({ facingMode: { exact: "user"} }, config, qrCodeSuccessCallback);

                    // Select back camera or fail with `OverconstrainedError`.
                    //html5QrCode.start({ facingMode: { exact: "environment"} }, config, qrCodeSuccessCallback);
                    //html5QrCode.start({ deviceId: { exact: cameraId} }, config, qrCodeSuccessCallback);
                    document.getElementById('qr_code').value = result;
                    </script>
                </div>
            </div>
            
            <!-- QR Scanner end here -->
            <div class="mb-3">
                <label for="qr_code" class="form-label">QR Code:</label>
                <input type="text" id="qr_code" name="qr_code" class="form-control" placeholder="Enter QR code number here" required >
            </div>
            <div class="mb-3">
                <label for="item_info" class="form-label">Select Item:</label>
                <select id="item_info" name="item_info" class="form-control" required data-live-search="true">
                    <option value="" data-box-price="" data-cutting-price="">Select an item</option>
                    <?php
                    // Database connection configuration (replace with your own credentials)
                    include("../config/config.php");
                    

                    /// Fetch data from the pricemanagement table for the current user's branch_id
                    $branchID = $_SESSION['branch_id'];
                    $sql = "SELECT item_id, item_name, brand, type, box_price, cutting_price FROM pricemanagement";
                    $stmt = $conn->prepare($sql);
                    //$stmt->bind_param("i", $branchID);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Include brand and type in the option text
                            // "Item ID: " . $row["item_id"] . " - Item Name: " . 
                            $optionText = $row["item_name"] . " - Brand: " . $row["brand"] . " - Type: " . $row["type"];
                            $boxPrice = $row["box_price"];
                            $cuttingPrice = $row["cutting_price"];
                            $selected = ($row['item_id'] == $itemID) ? 'selected' : '';
                            echo "<option value='" . $row["item_id"] . "' data-box-price='$boxPrice' data-cutting-price='$cuttingPrice' $selected>" . $optionText . "</option>";
                        }
                    }

                    // Close the database connection
                    $stmt->close();
                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="BuyingPrice" class="form-label">Buying Price</label>
                <input type="number" step="any" id="BuyingPrice" name="BuyingPrice" class="form-control" required value="<?php echo $BuyingPrice; ?>" onchange="GetPrice();" onkeydown="GetPrice();" onkeyup="GetPrice();">
            </div>
            <!-- <div class="mb-3">
                <label for="box_price" class="form-label">Box Price:</label>
                <input type="text" id="box_price" name="box_price" class="form-control" readonly value="<?php //echo $boxPrice; ?>">
            </div>
            <div class="mb-3">
                <label for="cutting_price" class="form-label">Cutting Price:</label>
                <input type="text" id="cutting_price" name="cutting_price" class="form-control" readonly value="<?php //echo $cuttingPrice; ?>">
            </div> -->
            
            <div class="mb-3">
                <label for="qty" class="form-label">Receiving Qty (KG):</label>
                <input type="number" id="qty" name="qty" class="form-control" required value="<?php echo $qty; ?>" onchange="GetPrice();" onkeydown="GetPrice();" onkeyup="GetPrice();" step="any">
            </div>
            <div class="mb-3">
                <label for="TotalPrice" class="form-label">Total Price:</label>
                <input type="number" id="TotalPrice" name="TotalPrice" class="form-control" required value="<?php echo $TotalPrice; ?>" readonly step="any">
            </div>
            <div class="row">
                <div class="col-md-2">
                    <input id="add_stock" class="btn btn-primary" type="submit" value="Save">
                </div>
            </div>
        </form>
            
    </div>

    <!-- Add this JavaScript code inside your HTML file, preferably just before the closing </body> tag -->
<script type="text/javascript">
    function GetPrice(){
        var BuyingPrice = $('#BuyingPrice').val();
        var qty = $('#qty').val();
        TotalPrice = BuyingPrice * qty;
        //TotalPrice = ReplaceNumberWithCommas(TotalPrice);
        $('#TotalPrice').val(TotalPrice);


    }
    function ReplaceNumberWithCommas(yourNumber) {
    //Seperates the components of the number
    var n= yourNumber.toString().split(".");
    //Comma-fies the first part
    n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //Combines the two sections
    return n.join(".");
}
    // Function to update box_price and cutting_price based on the selected item
    function updatePrices() {
        const itemInfoSelect = document.getElementById('item_info');
        const boxPriceInput = document.getElementById('box_price');
        const cuttingPriceInput = document.getElementById('cutting_price');
        
        // Get the selected item's data attributes
        const selectedItem = itemInfoSelect.options[itemInfoSelect.selectedIndex];
        const boxPrice = selectedItem.getAttribute('data-box-price');
        const cuttingPrice = selectedItem.getAttribute('data-cutting-price');
        
        // Update the input fields with the selected item's prices
        boxPriceInput.value = boxPrice;
        cuttingPriceInput.value = cuttingPrice;
    }
    
    // Attach the updatePrices function to the change event of the item_info select element
    const itemInfoSelect = document.getElementById('item_info');
    itemInfoSelect.addEventListener('change', updatePrices);
    
    // Initialize prices on page load
    updatePrices();
</script>
<!-- NEW SCRIPT FOR THE SEARCH BAR ON THE LIST OF ITEM ON RECEIVING -->
<script>
    $(document).ready(function () {
        // Initialize Select2 on your select element
        $("#item_info").select2();
    });
</script>

</body>
</html>
