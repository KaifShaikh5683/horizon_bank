
<?php

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/customer.php";
require_once __DIR__ . "/BankAccount.php";

// Database connection and objects
$database = new Database();
$connection = $database->connect();

$customer = new customer($connection);
$account = new BankAccount($connection);

// Read a positive customer ID
function readCustomerId(): int
{
    $id = filter_var(
        readline("Enter Customer ID: "),
        FILTER_VALIDATE_INT
    );

    if ($id === false || $id <= 0) {
        throw new InvalidArgumentException(
            "Customer ID must be a positive integer."
        );
    }

    return $id;
}

// Read and validate an amount
function readAmount(): float
{
    $input = trim(readline("Enter Amount: "));

    if (!is_numeric($input) || !is_finite((float)$input)) {
        throw new InvalidArgumentException(
            "Enter a valid numeric amount."
        );
    }

    $amount = (float)$input;

    if ($amount <= 0 || round($amount, 2) != $amount) {
        throw new InvalidArgumentException(
            "Amount must be positive and have at most 2 decimal places."
        );
    }

    return $amount;
}

// Main console application
try {

    while (true) {

        echo "\n";
        echo "1.  Create Customer\n";
        echo "2.  View All Customers\n";
        echo "3.  View Specific Customer\n";
        echo "4.  Update Customer Details\n";
        echo "5.  Delete Customer\n";
        echo "6.  Credit Money\n";
        echo "7.  Debit Money\n";
        echo "8.  Check Account Balance\n";
        echo "9.  View Transaction History\n";
        echo "10. Exit\n";
    

        $choice = trim(readline("Enter your choice: "));

        try {

            switch ($choice) {

                // CREATE CUSTOMER
                case "1":
                    echo "\n--- CREATE CUSTOMER ---\n";

                    $accountNumber = trim(
                        readline("Enter Account Number: ")
                    );

                    $name = trim(
                        readline("Enter Customer Name: ")
                    );

                    $email = trim(
                        readline("Enter Email: ")
                    );

                    $phone = trim(
                        readline("Enter Phone: ")
                    );

                    $address = trim(
                        readline("Enter Address: ")
                    );

                    $balance = readAmount();

                    if (
                        $accountNumber === "" ||
                        $name === "" ||
                        $email === "" ||
                        $phone === "" ||
                        $address === ""
                    ) {
                        throw new InvalidArgumentException(
                            "All customer fields are required."
                        );
                    }

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidArgumentException(
                            "Enter a valid email address."
                        );
                    }

                    if ($customer->createCustomer(
                        $accountNumber,
                        $name,
                        $email,
                        $phone,
                        $address,
                        $balance
                    )) {
                        echo "Customer created successfully!\n";
                    }
                    break;

                // VIEW ALL CUSTOMERS
                case "2":
                    echo "\n--- ALL CUSTOMERS ---\n";

                    $customers = $customer->getAllCustomers();

                    if (count($customers) === 0) {
                        echo "No customers found.\n";
                        break;
                    }

                    foreach ($customers as $c) {
                        echo "\nCustomer ID : "
                            . $c["customer_id"] . "\n";

                        echo "Account No  : "
                            . $c["account_number"] . "\n";

                        echo "Name        : "
                            . $c["customer_name"] . "\n";

                        echo "Email       : "
                            . $c["email"] . "\n";

                        echo "Phone       : "
                            . $c["phone"] . "\n";

                        echo "Address     : "
                            . $c["address"] . "\n";

                        echo "Balance     : Rs. "
                            . $c["balance"] . "\n";

                        echo "-----------------------------\n";
                    }
                    break;

                // VIEW SPECIFIC CUSTOMER
                case "3":
                    echo "\n--- CUSTOMER DETAILS ---\n";

                    $id = readCustomerId();
                    $details = $customer->getCustomerById($id);

                    if ($details !== null) {
                        foreach ($details as $key => $value) {
                            echo ucfirst($key) . " : "
                                . ($value ?? "N/A") . "\n";
                        }
                    } else {
                        echo "Customer not found.\n";
                    }
                    break;

                // UPDATE CUSTOMER
                case "4":
                    echo "\n--- UPDATE CUSTOMER ---\n";

                    $id = readCustomerId();

                    $name = trim(
                        readline("Enter New Name: ")
                    );

                    $phone = trim(
                        readline("Enter New Phone: ")
                    );

                    $address = trim(
                        readline("Enter New Address: ")
                    );

                    if (
                        $name === "" ||
                        $phone === "" ||
                        $address === ""
                    ) {
                        throw new InvalidArgumentException(
                            "Fields cannot be empty."
                        );
                    }

                    if ($customer->updateCustomer(
                        $id,
                        $name,
                        $phone,
                        $address
                    )) {
                        echo "Customer updated successfully!\n";
                    } else {
                        echo "Customer not found or no changes made.\n";
                    }
                    break;

                // DELETE CUSTOMER
                case "5":
                    echo "\n--- DELETE CUSTOMER ---\n";

                    $id = readCustomerId();

                    $confirm = strtolower(
                        trim(readline(
                            "Delete this customer? (yes/no): "
                        ))
                    );

                    if ($confirm !== "yes") {
                        echo "Deletion cancelled.\n";
                        break;
                    }

                    if ($customer->deleteCustomer($id)) {
                        echo "Customer deleted successfully!\n";
                    } else {
                        echo "Customer not found.\n";
                    }
                    break;

                // CREDIT MONEY
                case "6":
                    echo "\n--- CREDIT MONEY ---\n";

                    $id = readCustomerId();
                    $amount = readAmount();

                    if ($account->credit($id, $amount)) {
                        echo "Money credited successfully!\n";
                    } else {
                        echo "Credit failed: customer not found.\n";
                    }
                    break;

                // DEBIT MONEY
                case "7":
                    echo "\n--- DEBIT MONEY ---\n";

                    $id = readCustomerId();
                    $amount = readAmount();

                    if ($account->debit($id, $amount)) {
                        echo "Money debited successfully!\n";
                    } else {
                        echo "Debit failed: customer not found "
                            . "or insufficient balance.\n";
                    }
                    break;

                // CHECK BALANCE
                case "8":
                    echo "\n--- ACCOUNT BALANCE ---\n";

                    $id = readCustomerId();
                    $details = $account->getBalance($id);

                    if ($details !== null) {
                        echo "Customer: "
                            . $details["customer_name"] . "\n";

                        echo "Account: "
                            . $details["account_number"] . "\n";

                        echo "Balance: Rs. "
                            . $details["balance"] . "\n";
                    } else {
                        echo "Customer not found.\n";
                    }
                    break;

                // TRANSACTION HISTORY
                case "9":
                    echo "\n--- TRANSACTION HISTORY ---\n";

                    $id = readCustomerId();

                    // Verify that the customer exists.
                    if ($customer->getCustomerById($id) === null) {
                        echo "Customer not found.\n";
                        break;
                    }

                    $history = $account->getTransactionHistory($id);

                    if (count($history) === 0) {
                        echo "No transactions found.\n";
                        break;
                    }

                    foreach ($history as $t) {
                        echo "\nTransaction ID: "
                            . $t["transaction_id"] . "\n";

                        echo "Type: "
                            . $t["transaction_type"] . "\n";

                        echo "Amount: Rs. "
                            . $t["amount"] . "\n";

                        echo "Date: "
                            . $t["transaction_date"] . "\n";

                        echo "-----------------------------\n";
                    }
                    break;

                // EXIT
                case "10":
                    echo "Thank you for using Horizon Bank!\n";
                    break 2;

                default:
                    echo "Invalid choice. Select 1 to 10.\n";
            }

        } catch (InvalidArgumentException $e) {
            echo "Input error: " . $e->getMessage() . "\n";

        } catch (mysqli_sql_exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
        }
    }

} finally {
    $connection->close();
}
