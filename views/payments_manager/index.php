<?php include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../public/config.php';
?>
<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payments Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Payments Manager</h1>

        <?php if (empty($payments)): ?>
            <div class="alert alert-info">No payments found.</div>
        <?php else: ?>
            <table id="paymentsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Note</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['id']) ?></td>
                            <td><?= htmlspecialchars($payment['client_id']) ?></td>
                            <td><?= htmlspecialchars(number_format($payment['amount'], 2)) ?></td>
                            <td><?= htmlspecialchars($payment['method']) ?></td>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                            <td><?= nl2br(htmlspecialchars($payment['note'])) ?></td>
                            <td><?= htmlspecialchars($payment['created_at']) ?></td>
                            <td>
                                <a href="<?= url('payments-manager/edit?id=' . $payment['id']) ?>"
                                    class="btn btn-sm btn-primary">Edit</a>
                                <a href="<?= url('payments-manager/delete?id=' . $payment['id']) ?>"
                                    class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#paymentsTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });
    </script>
</body>

</html>


<?php include __DIR__ . '/../layouts/footer.php'; ?>