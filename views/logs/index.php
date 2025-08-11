<?php include __DIR__ . '/../layouts/header.php'; ?>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>


<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history"></i> Activity Logs</h1>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter"></i> Filters</div>
    <div class="card-body">
        <form id="logsFilterForm">
            <div class="row">
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" name="date_from">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo" name="date_to">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="reset" class="btn btn-outline-secondary ms-2" id="resetFilters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="fas fa-table"></i> Activity Logs</div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="logsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date/Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for full description -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="descriptionModalLabel">Full Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDescriptionContent">Loading...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var table = $('#logsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= url('logs/datatable') ?>',
                type: 'POST',
                data: function (d) {
                    d.date_from = $('#dateFrom').val();
                    d.date_to = $('#dateTo').val();
                }
            },
            columns: [
                { data: 'id' },
                { data: 'user' },
                { data: 'action' },
                {
                    data: 'description',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return data + ' <a href="#" class="text-primary view-full" data-id="' + row.id + '"><i class="fas fa-eye"></i></a>';
                        }
                        return data;
                    }
                },
                { data: 'ip_address' },
                { data: 'created_at' }
            ],
            order: [[0, 'desc']],
            responsive: true
        });

        // Filter submit reloads table with validation
        $('#logsFilterForm').on('submit', function (e) {
            e.preventDefault();

            const fromDate = $('#dateFrom').val();
            const toDate = $('#dateTo').val();

            if (fromDate && toDate && new Date(toDate) < new Date(fromDate)) {
                alert("'To Date' cannot be earlier than 'From Date'. Please correct the dates.");
                return;
            }

            table.ajax.reload();
        });

        // Reset filters and reload table
        $('#resetFilters').on('click', function () {
            $('#logsFilterForm')[0].reset();
            table.ajax.reload();
        });

        // Show full description modal
        $('#logsTable').on('click', '.view-full', function (e) {
            e.preventDefault();
            var rowData = table.row($(this).closest('tr')).data();
            $('#modalDescriptionContent').text(rowData.full_description);
            $('#descriptionModal').modal('show');
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>