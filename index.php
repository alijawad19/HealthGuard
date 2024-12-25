<?php
session_start();
require('config/db_connection.php');

// Generate a CSRF token if one doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If a shortened URL code is accessed, redirect to the original URL
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $code = htmlspecialchars(trim($_GET['code']));

  // Search for the shortened code in the database
  $stmt = $conn->prepare("SELECT * FROM payment_link WHERE shortened_code = :shortened_code LIMIT 1");
  $stmt->execute(['shortened_code' => $code]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
      $stmt2 = $conn->prepare("SELECT payment_status FROM proposals WHERE application_id = :application_id LIMIT 1");
      $stmt2->execute(['application_id' => $row['application_id']]);
      $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

      if ($row2['payment_status'] == 1) {
          $_SESSION['row'] = '';
      } else {
          $_SESSION['row'] = $row;
      }
  } else {
    $_SESSION['row'] = '';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]);
        die();
    }

  if ($_POST['payment'] == 'paid') {
    $cardName = isset($_POST['nameOnCard']) ? htmlspecialchars(trim($_POST['nameOnCard'])) : null;
    $cardNumber = isset($_POST['cardNumber']) ? htmlspecialchars(trim($_POST['cardNumber'])) : null;
    $cardExpiry = isset($_POST['expiryDate']) ? htmlspecialchars(trim($_POST['expiryDate'])) : null;
    $cvv = isset($_POST['cvv']) ? htmlspecialchars(trim($_POST['cvv'])) : null;
    $application_id = isset($_POST['application_id']) ? htmlspecialchars(trim($_POST['application_id'])) : null;
    $response_url = isset($_POST['response_url']) ? htmlspecialchars(trim($_POST['response_url'])) : null;
    $response_url = html_entity_decode($response_url);
    $response_url = $response_url . '&payment=success';

    if (is_null($cardName)) {
      echo json_encode([
        'success' => false,
        'error' => 'Name is required'
      ]);
      die();
    }

    if (is_null($cardNumber) || $cardNumber != '4111111111111111111'
    ) {
      echo json_encode([
        'success' => false,
        'error' => 'Wrong card number'
      ]);
      die();
    }

    if (is_null($cardExpiry)) {
      echo json_encode([
        'success' => false,
        'error' => 'Card expiry date is required'
      ]);
      die();
    }

    // Validate format of card expiry
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $cardExpiry)) {
      echo json_encode([
        'success' => false,
        'error' => 'Invalid expiry date format. Please use MM/YY format.'
      ]);
      die();
    }

    // Split the expiry date into month and year
    list($expMonth, $expYear) = explode('/', $cardExpiry);

    // Get current year in two-digit format
    $currentYear = date('y');
    $currentMonth = date('m');

    // Check if card is expired
    if ($expYear < $currentYear || ($expYear == $currentYear && $expMonth < $currentMonth)
    ) {
      echo json_encode([
        'success' => false,
        'error' => 'Card expired.'
      ]);
      die();
    }

    if (is_null($cvv) || !is_numeric($cvv)
    ) {
      echo json_encode([
        'success' => false,
        'error' => 'CVV is required'
      ]);
      die();
    }

    $stmt = $conn->prepare("SELECT * FROM proposals WHERE application_id = :application_id LIMIT 1");
    $stmt->execute(['application_id' => $application_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $tenure = $row['tenure'];

    $start_date = new DateTime();
    $start_date->setTime(0, 0,
      0
    );

    $end_date = clone $start_date;
    $end_date->modify("+{$tenure} years");
    $end_date->modify("-1 day");

    $start_date = $start_date->format('Y-m-d');
    $end_date = $end_date->format('Y-m-d');

    $updateStatement = $conn->prepare("UPDATE proposals SET payment_status = :payment_status, start_date = :start_date, end_date = :end_date WHERE application_id = :application_id");
    $isUpdated = $updateStatement->execute([
      'payment_status' => '1',
      'start_date' => $start_date,
      'end_date' => $end_date,
      'application_id' => $application_id
    ]);

    if ($isUpdated) {
      $sumInsured = $row['sum_insured'];
      $premiumPaid = $row['total_premium'];

      $insertPolicyStmt = $conn->prepare("INSERT INTO policies (application_id, issuance_date, expiry_date, sum_insured, premium_paid, policy_status) 
            VALUES (:application_id, :issuance_date, :expiry_date, :sum_insured, :premium_paid, :policy_status)");

      $isPolicyInserted = $insertPolicyStmt->execute([
        'application_id' => $application_id,
        'issuance_date' => $start_date,
        'expiry_date' => $end_date,
        'sum_insured' => $sumInsured,
        'premium_paid' => $premiumPaid,
        'policy_status' => 'active'
      ]);

      if ($isPolicyInserted) {
        echo json_encode([
          'success' => true,
          'error' => '',
          'url' => $response_url
        ]);
        die();
      } else {
        echo json_encode([
          'success' => false,
          'error' => 'Policy creation failed. Please try again.'
        ]);
        die();
      }
    } else {
      echo json_encode([
        'success' => false,
        'error' => 'Payment failed. Please try again.'
      ]);
      die();
    }
  } else {
    $response_url = isset($_POST['response_url']) ? htmlspecialchars(trim($_POST['response_url'])) : null;
    $response_url = html_entity_decode($response_url);
    $response_url = $response_url . '&payment=failed';

    echo json_encode([
      'success' => true,
      'error' => '',
      'url' => $response_url
    ]);
    die();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Page</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <?php if (isset($_SESSION['row']) && !empty($_SESSION['row'])) { ?>
          <div class="card shadow-lg p-4">
            <h4 class="text-center mb-4">Payment Details</h4>
            <form id="make-payment-form">
              <!-- CSRF Token -->
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

              <!-- Name on Card -->
              <input type="hidden" id="application_id" value="<?php echo $_SESSION['row']['application_id'] ?>">
              <input type="hidden" id="response_url" value="<?php echo $_SESSION['row']['response_url'] ?>">
              <div class="mb-3">
                <label for="nameOnCard" class="form-label">Name on Card</label>
                <input type="text" class="form-control" id="nameOnCard" placeholder="John Doe" required>
              </div>

              <!-- Card Number -->
              <div class="mb-3">
                <label for="cardNumber" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
              </div>

              <!-- Expiry Date and CVV -->
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="expiryDate" class="form-label">Expiry Date</label>
                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" maxlength="5" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="3" required>
                  </div>
                </div>
              </div>

              <!-- Buttons Container -->
              <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-danger w-100" id="cancelPaymentButton">Cancel Payment</button>
                <button type="submit" class="btn btn-success w-100">Pay ₹<?php echo $_SESSION['row']['total_premium']; ?></button>
              </div>

              <!-- Confirmation Modal -->
              <div class="modal fade" id="cancelConfirmationModal" tabindex="-1" aria-labelledby="cancelConfirmationLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="cancelConfirmationLabel">Cancel Payment</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      Are you sure you want to cancel this payment?
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                      <button type="button" class="btn btn-danger" id="confirmCancelPayment">Yes, Cancel Payment</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        <?php } else { ?>
          <div class="card shadow-lg p-4">
            <h4 class="text-center mb-4">Resource not found!</h4>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#cancelPaymentButton').click(function() {
        $('#cancelConfirmationModal').modal('show');
      });

      $('#make-payment-form').submit(function(event) {
        event.preventDefault();

        const name = $('#nameOnCard').val();
        const cardNumber = $('#cardNumber').val();
        const cardExpiry = $('#expiryDate').val();
        const cvv = $('#cvv').val();
        const application_id = $('#application_id').val();
        const response_url = $('#response_url').val();

        $.ajax({
          url: '',
          method: 'POST',
          data: {
            nameOnCard: name,
            cardNumber: cardNumber,
            expiryDate: cardExpiry,
            cvv: cvv,
            application_id: application_id,
            response_url: response_url,
            payment: 'paid',
            csrf_token: $('input[name="csrf_token"]').val() // Include CSRF token
          },
          success: function(response) {
            const responseData = JSON.parse(response);

            if (responseData.success) {
              showErrorModal('Payment successful');
              window.location.href = responseData.url;
            } else {
              const errorMessage = responseData.error || 'Something went wrong.';
              showErrorModal(errorMessage);
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            showErrorModal('Something went wrong.');
          }
        });
      });

      $('#confirmCancelPayment').click(function() {
        $('#cancelConfirmationModal').modal('hide');

        const application_id = $('#application_id').val();
        const response_url = $('#response_url').val();

        $.ajax({
          url: '',
          method: 'POST',
          data: {
            application_id: application_id,
            response_url: response_url,
            payment: 'unpaid',
            csrf_token: $('input[name="csrf_token"]').val() // Include CSRF token
          },
          success: function(response) {
            const responseData = JSON.parse(response);

            if (responseData.success) {
              showErrorModal('Payment has been canceled.');
              window.location.href = responseData.url;
            } else {
              const errorMessage = responseData.error || 'Failed to cancel payment.';
              showErrorModal(errorMessage);
            }
          },
          error: function() {
            showErrorModal('Something went wrong while canceling the payment.');
          }
        });
      });

      function showErrorModal(message) {
        $('#errorModal .modal-body').text(message);
        $('#errorModal').modal('show');
      }
    });
  </script>
  <!-- Bootstrap modal for error display -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="errorModalLabel">Success!</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Error message will be injected here by JavaScript -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>