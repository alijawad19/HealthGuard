# HealthGuard

HealthGuard is a PHP-based simulation of a health insurance API system. It replicates essential functionalities of health insurance, including premium calculation, proposal creation, payment link generation, and policy document download.

## Features

- **Premium Calculation:** Calculate premium based on tenure, sum insured, gender, and age.
- **Proposal Creation:** Collect user and nominee details to generate application IDs.
- **Payment Link Generation:** Generate payment links with a response URL for seamless transactions.
- **PDF Download:** Download policy documents post-payment.

## API Endpoints

### 1. Premium Calculation

**Endpoint:** `/premium`
**Method:** `POST`

**Request:**

```json
{
    "age": "28",
    "tenure": "1",
    "gender": "male",
    "sum_insured": 500000
}
```

**Response:**

```json
{
    "status": "success",
    "error": null,
    "data": {
        "net_premium": 1000,
        "gst": 180,
        "total_premium": 1180,
        "tenure": 1,
        "age": 28,
        "gender": "male",
        "sum_insured": 500000
    }
}
```

### 2. Proposal Creation

**Endpoint:** `/proposal`
**Method:** `POST`

**Request:**

```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "address": "123 Main Street",
    "tenure": "1",
    "gender": "male",
    "sum_insured": "500000",
    "pincode": "591305",
    "contact": "9876543210",
    "dob": "2000-01-01",
    "nomineeDetails": {
        "name": "Jane Smith",
        "relation": "Sister",
        "dob": "1995-06-15",
        "contact": "9123456789"
    }
}
```

**Response:**

```json
{
    "status": "success",
    "error": null,
    "data": {
        "message": "Proposal created successfully",
        "application_id": 73398,
        "net_premium": 950,
        "gst": 171,
        "total_premium": 1121,
        "tenure": 1,
        "age": 24,
        "gender": "male",
        "sum_insured": 500000,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "address": "123 Main Street",
        "pincode": "591305",
        "contact": "9876543210",
        "dob": "2000-01-01",
        "nomineeDetails": {
            "name": "Jane Smith",
            "relation": "Sister",
            "dob": "1995-06-15",
            "contact": "9123456789"
        },
        "url": "http://healthguard/payment?application_id=73398&total_premium=1121"
    }
}
```

### 3. Payment Link Generation

**Endpoint:** `/payment?application_id={application_id}&total_premium={total_premium}&response_url={encoded_url}`
**Method:** `GET`

**Response:**

```json
{
    "status": "success",
    "error": null,
    "data": {
        "message": "Payment link created successfully",
        "application_id": 73398,
        "total_premium": 1121,
        "payment_url": "http://healthguard?code=zfu8mvl9t0kyb7"
    }
}
```

### 4. PDF Download

**Endpoint:** `/downloadpdf?application_id={application_id}`
**Method:** `GET`

**Request Parameters:**

- `application_id` (string) - The application ID from the proposal.

**Response:**
A PDF file containing the policy document.

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/alijawad19/HealthGuard.git
   cd HealthGuard
   ```
2. Install dependencies (if applicable):

   ```bash
   composer install
   ```
3. Configure the application:Update the database connection and other settings in the `config.php` file.
4. Start the server:

   ```bash
   php -S localhost:8000
   ```

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.
