import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

const language = {
    emptyTable: 'Gösterilecek kayıt bulunamadı.',
    info: '_TOTAL_ kayıttan _START_–_END_ arası gösteriliyor',
    infoEmpty: 'Kayıt yok',
    infoFiltered: '(_MAX_ kayıt içinden filtrelendi)',
    lengthMenu: 'Sayfada _MENU_ kayıt göster',
    loadingRecords: 'Yükleniyor…',
    processing: 'Yükleniyor…',
    search: 'Ara:',
    zeroRecords: 'Eşleşen kayıt bulunamadı.',
    paginate: { first: 'İlk', last: 'Son', next: 'Sonraki', previous: 'Önceki' },
};

export function initializeTables(root = document) {
    root.querySelectorAll('.js-data-table').forEach((element) => {
        if (DataTable.isDataTable(element)) {
            return;
        }

        const configElement = document.getElementById(element.dataset.config);

        if (!configElement) {
            console.error('Tablo konfigürasyonu bulunamadı:', element.id);
            return;
        }

        let config;

        try {
            config = JSON.parse(configElement.textContent);
        } catch (error) {
            console.error('Tablo konfigürasyonu okunamadı:', element.id, error);
            return;
        }

        const filters = [...document.querySelectorAll('[data-table-filter]')]
            .filter((filter) => filter.dataset.tableTarget === element.id);
        const table = new DataTable(element, {
            processing: true,
            serverSide: true,
            searchDelay: 350,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            ...config,
            language: {
                ...language,
                ...config.language,
                paginate: { ...language.paginate, ...config.language?.paginate },
            },
            ajax: {
                url: element.dataset.source,
                data(data) {
                    filters.forEach((filter) => {
                        data[filter.dataset.tableFilter] = filter.value;
                    });
                },
            },
        });

        filters.forEach((filter) => {
            filter.addEventListener('change', () => table.ajax.reload());
        });
    });
}
