<?php
$isLoginPage = (basename($_SERVER['PHP_SELF']) == 'login.php' || strpos($_SERVER['REQUEST_URI'], 'login') !== false);
?>

<?php if (!$isLoginPage): ?>
    </main>
    </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!$isLoginPage): ?>
    <script>
        // Bulk selection functionality
        function toggleBulkActions() {
            const checkboxes = document.querySelectorAll('.license-checkbox:checked');
            const bulkActions = document.querySelector('.bulk-actions');

            if (checkboxes.length > 0) {
                bulkActions.style.display = 'block';
                document.getElementById('selected-count').textContent = checkboxes.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // Select all functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.license-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            toggleBulkActions();
        }

        // Export functionality
        function exportData(format) {
            const selectedIds = [];
            document.querySelectorAll('.license-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/Practice_php/public/export';

            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            form.appendChild(formatInput);

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = selectedIds.join(',');
            form.appendChild(idsInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Bulk update functionality
        function bulkUpdate() {
            const selectedIds = [];
            document.querySelectorAll('.license-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });

            if (selectedIds.length === 0) {
                alert('Please select at least one license to update.');
                return;
            }

            const newDate = document.getElementById('bulk-valid-to').value;
            if (!newDate) {
                alert('Please select a new expiry date.');
                return;
            }

            if (confirm(`Update ${selectedIds.length} licenses with new expiry date: ${newDate}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/Practice_php/public/activation-codes/bulk-update';

                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'ids';
                idsInput.value = selectedIds.join(',');
                form.appendChild(idsInput);

                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = 'valid_to';
                dateInput.value = newDate;
                form.appendChild(dateInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }
    </script>
<?php endif; ?>
</body>

</html>