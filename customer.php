
<?php

class Customer
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    // INSERT: Register a customer
    public function createCustomer(
        string $accountNumber,
        string $name,
        string $email,
        string $phone,
        string $address,
        float $balance
    ): bool {
        if (!is_finite($balance) || $balance < 0) {
            throw new InvalidArgumentException(
                "Opening balance cannot be negative."
            );
        }

        $sql = "INSERT INTO customers
                (account_number, customer_name, email,
                 phone, address, balance)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);

        $stmt->bind_param(
            "sssssd",
            $accountNumber,
            $name,
            $email,
            $phone,
            $address,
            $balance
        );

        $stmt->execute();
        $stmt->close();

        return true;
    }

    // SELECT: Display all customers
    public function getAllCustomers(): array
    {
        $sql = "SELECT * FROM customers ORDER BY customer_id";

        $result = $this->connection->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // SELECT: Find a specific customer
    public function getCustomerById(int $customerId): ?array
    {
        $sql = "SELECT * FROM customers WHERE customer_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        $stmt->close();

        return $customer ?: null;
    }

    // UPDATE: Change customer details
    public function updateCustomer(
        int $customerId,
        string $name,
        string $phone,
        string $address
    ): bool {
        $sql = "UPDATE customers
                SET customer_name = ?, phone = ?, address = ?
                WHERE customer_id = ?";

        $stmt = $this->connection->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $name,
            $phone,
            $address,
            $customerId
        );

        $stmt->execute();
        $updated = $stmt->affected_rows > 0;

        $stmt->close();

        return $updated;
    }

    // DELETE: Remove a customer
    public function deleteCustomer(int $customerId): bool
    {
        $sql = "DELETE FROM customers WHERE customer_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();

        $deleted = $stmt->affected_rows > 0;

        $stmt->close();

        return $deleted;
    }
}
