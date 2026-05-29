# AJAX MySQL Product Inserter

A minimal PHP/jQuery snippet that inserts an array of products into MySQL via AJAX — no page refresh.

## Quick Setup

### 1. Create the database table

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Configure the PHP script

Open `insert_products.php` and update the credentials at the top:

```php
$host   = 'localhost';       // Your MySQL host
$dbname = 'your_database';   // Your database name
$user   = 'your_username';   // Your MySQL username
$pass   = 'your_password';   // Your MySQL password
$table  = 'products';        // Your table name
```

### 3. Place files on your server

Put both `insert_products.php` and `index.html` in the same directory on any PHP-enabled server (Apache, Nginx, XAMPP, MAMP, etc.).

### 4. Open and test

Navigate to `index.html` in your browser and click **Insert Products**. You should see a success message and three rows in your `products` table.

## How to modify the product array

In `index.html`, edit the `products` variable:

```js
var products = [
    { product_name: "Widget A", price: 9.99,  quantity: 50 },
    { product_name: "New Item", price: 25.00, quantity: 10 }
];
```

## Adding more fields

1. Add the column to your MySQL table:
   ```sql
   ALTER TABLE products ADD COLUMN category VARCHAR(100);
   ```

2. Add the field to the JS array:
   ```js
   { product_name: "Widget A", price: 9.99, quantity: 50, category: "Electronics" }
   ```

3. Update the PHP INSERT query and bindings:
   ```php
   $stmt = $pdo->prepare(
       "INSERT INTO `$table` (product_name, price, quantity, category)
        VALUES (:product_name, :price, :quantity, :category)"
   );

   $stmt->execute([
       ':product_name' => $product['product_name'],
       ':price'        => (float) $product['price'],
       ':quantity'     => (int) $product['quantity'],
       ':category'     => $product['category'],
   ]);
   ```

## Files

| File | Purpose |
|------|---------|
| `index.html` | Front-end: product array, jQuery AJAX call, trigger button |
| `insert_products.php` | Back-end: PDO connection, prepared statement insert |
| `README.md` | This file |

## Requirements

- PHP 7.4+ with PDO MySQL extension enabled
- MySQL 5.7+ or MariaDB 10.3+
- jQuery 3.x (loaded from CDN in the HTML)
