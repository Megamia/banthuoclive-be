<div data-control="toolbar">
    <a href="<?= Backend::url('betod/livotec/category/create') ?>" class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>
    <form id="importCsvForm" enctype="multipart/form-data"
        style="display: inline-block; margin-left: 10px;margin-right: 20px;">
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
    <script>
        document.getElementById('importCsvInput').addEventListener('change', function (event) {
            event.preventDefault();
            let formData = new FormData();
            formData.append('csv_file', this.files[0]);

            let csrfToken = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            csrfToken = csrfToken ? csrfToken[1] : '';

            fetch('http://127.0.0.1:8000/apiImport/import-category', {
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
</div>