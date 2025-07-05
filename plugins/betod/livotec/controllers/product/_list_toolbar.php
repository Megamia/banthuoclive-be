<div data-control="toolbar" style="display:flex">
    <a href="<?= Backend::url('betod/livotec/product/create') ?>" class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>

    <form id="importCsvForm" enctype="multipart/form-data"
        style="display: flex; margin-left: 10px;margin-right: 20px;">
        <?= csrf_field() ?>
        <input type="file" name="csv_file" accept=".csv" style="display: none;" id="importCsvInput">
        <button type="button" class="btn btn-primary oc-icon-upload"
            onclick="document.getElementById('importCsvInput').click();">
            Import CSV
        </button>
    </form>

    <button class="btn btn-default oc-icon-trash-o" data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>" data-list-checked-trigger
        data-list-checked-request data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>

    <form id="filterForm" style="display: flex; justify-content:center; margin-right: 20px;">
        <select name="filter_status" class="form-control" style="display: flex; width: 180px;">
            <option value="">-- Lọc theo trạng thái --</option>
            <option value="out_of_stock">Hết hàng</option>
            <option value="best_seller">Bán chạy</option>
            <option value="in_stock">Còn hàng</option>
        </select>
        <button type="submit" class="btn btn-secondary oc-icon-search">Lọc</button>
    </form>


    <script>
        document.getElementById('importCsvInput').addEventListener('change', function (event) {
            event.preventDefault();
            let formData = new FormData();
            formData.append('csv_file', this.files[0]);

            let csrfToken = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            csrfToken = csrfToken ? csrfToken[1] : '';

            fetch('http://127.0.0.1:8000/apiImport/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })

                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert("Import thất bại: " + data.error);
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        });
    </script>
    <script>
        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const status = this.filter_status.value;

            const params = new URLSearchParams(window.location.search);
            if (status) {
                params.set('filter_status', status);
            } else {
                params.delete('filter_status');
            }

            window.location.search = params.toString();
        });
        window.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('filter_status');
            if (status) {
                document.querySelector('#filterForm select[name="filter_status"]').value = status;
            }
        });
    </script>

</div>