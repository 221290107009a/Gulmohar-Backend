<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    /*$conn = new mysqli("127.0.0.1", "abfnqoss_phxsolution", "Phx@Dbmaster@1404", "abfnqoss_sanghoapp");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['code']) && $_POST['code'] == 'PAYMENT_SUCCESS') {
    
        $merchantTransactionId = $_POST['transactionId'];
        $sql = "UPDATE payments SET 
                payment_status = 'PAYMENT_SUCCESS',
                provider_reference_id = '{$_POST['providerReferenceId']}'
                WHERE transaction_id = '$merchantTransactionId'";
        $conn->query($sql);
    }
    $conn->close();*/
    echo "Confirm Payment";
    return;
}
?>
