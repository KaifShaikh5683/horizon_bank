
<?php

class BankAccount
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    // CREDIT or DEBIT using a database transaction
    private function processTransaction(
        int $customerId,
        float $amount,
        string $type
    ): bool {
        if (!is_finite($amount) || $amount <= 0) {
            throw new InvalidArgumentException(
                "Amount must be greater than zero."
            );
        }

        // Store amounts with two decimal places.
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException("Invalid amount.");
        }

        $this->connection->begin_transaction();

        try {
            // Lock the account row during the operation.
            $sql = "SELECT balance FROM customers
                    WHERE customer_id = ? FOR UPDATE";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $customerId);
            $stmt->execute();

            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
            $stmt->close();

            if (!$customer) {
                $this->connection->rollback();
                return false;
            }

            $balance = (float) $customer["balance"];

            if ($type === "DEBIT" && $balance < $amount) {
                $this->connection->rollback();
                return false;
            }

            // Update the balance.
            if ($type === "CREDIT") {
                $sql = "UPDATE customers
                        SET balance = balance + ?
                        WHERE customer_id = ?";
            } else {
                $sql = "UPDATE customers
                        SET balance = balance - ?
                        WHERE customer_id = ?";
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("di", $amount, $customerId);
            $stmt->execute();
            $stmt->close();

            // Record the financial transaction.
            $sql = "INSERT INTO transactions
                    (customer_id, transaction_type, amount)
                    VALUES (?, ?, ?)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("isd", $customerId, $type, $amount);
            $stmt->execute();
            $stmt->close();

            $this->connection->commit();

            return true;

        } catch (Throwable $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    // Deposit money
    public function credit(int $customerId, float $amount): bool
    {
        return $this->processTransaction(
            $customerId,
            $amount,
            "CREDIT"
        );
    }

    // Withdraw money
    public function debit(int $customerId, float $amount): bool
    {
        return $this->processTransaction(
            $customerId,
            $amount,
            "DEBIT"
        );
    }

    // Check account balance
    public function getBalance(int $customerId): ?array
    {
        $sql = "SELECT customer_id, account_number,
                       customer_name, balance
                FROM customers
                WHERE customer_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $details = $result->fetch_assoc();

        $stmt->close();

        return $details ?: null;
    }

    // Display transaction history
    public function getTransactionHistory(int $customerId): array
    {
        $sql = "SELECT transaction_id, transaction_type,
                       amount, transaction_date
                FROM transactions
                WHERE customer_id = ?
                ORDER BY transaction_id DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $history = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $history;
    }
}
