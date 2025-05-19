document.addEventListener('DOMContentLoaded', async () => {
    await loadPartners();
    
    await loadSalesHistory();
    
    document.getElementById('applyFilters').addEventListener('click', async () => {
        await loadSalesHistory(getFilters());
    });
    
    document.getElementById('resetFilters').addEventListener('click', async () => {
        resetFilters();
        await loadSalesHistory();
    });
    
    document.getElementById('exportBtn').addEventListener('click', exportToExcel);
});

async function loadPartners() {
    try {
        const response = await fetch('db.php?action=getAllPartners');
        const partners = await response.json();
        
        const select = document.getElementById('partnerFilter');
        partners.forEach(partner => {
            const option = document.createElement('option');
            option.value = partner.id;
            option.textContent = partner.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Ошибка загрузки партнеров:', error);
    }
}

async function loadSalesHistory(filters = {}) {
    try {
        let url = 'db.php?action=getAllSalesHistory';
        
        if (filters.dateFrom) url += `&dateFrom=${filters.dateFrom}`;
        if (filters.dateTo) url += `&dateTo=${filters.dateTo}`;
        if (filters.minAmount) url += `&minAmount=${filters.minAmount}`;
        if (filters.maxAmount) url += `&maxAmount=${filters.maxAmount}`;
        if (filters.partnerId) url += `&partnerId=${filters.partnerId}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Ошибка загрузки данных');
        
        const data = await response.json();
        renderSalesTable(data.sales);
        updateSummary(data.summary);
        
    } catch (error) {
    }
}

function renderSalesTable(sales) {
    const tbody = document.querySelector('#salesTable tbody');
    tbody.innerHTML = '';

    sales.forEach(sale => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${new Date(sale.sale_date).toLocaleDateString('ru-RU')}</td>
            <td><span class="partner-name">${sale.partner_name}</span></td>
            <td>${parseFloat(sale.amount).toLocaleString('ru-RU')} ₽</td>
            <td>
                <button class="btn primary" onclick="showSaleDetails(${sale.id})">Подробнее</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateSummary(summary) {
    document.getElementById('totalSales').textContent = summary.count;
    document.getElementById('totalAmount').textContent = summary.total_amount.toLocaleString('ru-RU') + ' ₽';
    document.getElementById('averageAmount').textContent = summary.avg_amount.toLocaleString('ru-RU') + ' ₽';
    document.getElementById('firstSale').textContent = summary.first_date ? new Date(summary.first_date).toLocaleDateString('ru-RU') : '-';
    document.getElementById('lastSale').textContent = summary.last_date ? new Date(summary.last_date).toLocaleDateString('ru-RU') : '-';
}

function getFilters() {
    return {
        dateFrom: document.getElementById('dateFrom').value,
        dateTo: document.getElementById('dateTo').value,
        minAmount: document.getElementById('minAmount').value,
        maxAmount: document.getElementById('maxAmount').value,
        partnerId: document.getElementById('partnerFilter').value
    };
}

function resetFilters() {
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('minAmount').value = '';
    document.getElementById('maxAmount').value = '';
    document.getElementById('partnerFilter').value = '';
}

function showSaleDetails(saleId) {
    alert(`Подробная информация о покупке #${saleId}`);
}

function exportToExcel() {
    alert('Экспорт в Excel будет реализован');
}