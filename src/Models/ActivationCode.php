<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1 class="my-4">Activation Codes</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Manage Activation Codes</h5>
                <a href="/Practice_php/public/activation-codes/create" class="btn btn-primary">Add New Code</a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter and Search Form -->
            <form method="get" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by name or license" value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select name="filter" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= ($filter ?? 'all') === 'all' ? 'selected' : '' ?>>All Licenses</option>
                            <option value="active" <?= ($filter ?? 'all') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="expired" <?= ($filter ?? 'all') === 'expired' ? 'selected' : '' ?>>Expired
                            </option>
                            <option value="expiring" <?= ($filter ?? 'all') === 'expiring' ? 'selected' : '' ?>>Expiring
                                Soon</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Activation Codes Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>License</th>
                            <th>Valid From</th>
                            <th>Valid To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($codes as $code): ?>
                            <tr>
                                <td><?= htmlspecialchars($code['id']) ?></td>
                                <td><?= htmlspecialchars($code['name']) ?></td>
                                <td><?= htmlspecialchars($code['license']) ?></td>
                                <td><?= htmlspecialchars($code['valid_from']) ?></td>
                                <td><?= htmlspecialchars($code['valid_to']) ?></td>
                                <td>
                                    <?php
                                    $validTo = strtotime($code['valid_to']);
                                    $now = time();
                                    if ($validTo < $now) {
                                        echo '<span class="badge bg-danger">Expired</span>';
                                    } elseif ($validTo - $now <= 7 * 24 * 60 * 60) {
                                        echo '<span class="badge bg-warning">Expiring Soon</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Active</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="/Practice_php/public/activation-codes/edit?id=<?= $code['id'] ?>"
                                        class="btn btn-sm btn-primary">Edit</a>
                                    <form action="/Practice_php/public/activation-codes/delete?id=<?= $code['id'] ?>"
                                        method="POST" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total > $perPage): ?>
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $i ?>&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>