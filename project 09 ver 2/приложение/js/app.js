const API_URL = 'db.php';

document.addEventListener('DOMContentLoaded', async () => {
    await loadPartners();
    
    document.getElementById('addPartnerBtn')?.addEventListener('click', () => {
        window.location.href = 'partner-form.html';
    });
    
    const partnerForm = document.getElementById('partnerForm');
    if (partnerForm) {
        partnerForm.addEventListener('submit', handleFormSubmit);
        
        document.getElementById('cancelBtn')?.addEventListener('click', () => {
            window.location.href = 'index.html';
        });
        
        const partnerId = new URLSearchParams(window.location.search).get('id');
        if (partnerId) {
            document.getElementById('formTitle').textContent = 'Редактирование партнёра';
            await loadPartnerData(partnerId);
        }
    }
});

async function loadPartners() {
    const partners = await getAllPartners();
    const tableBody = document.querySelector('#partnersTable tbody');
    
    if (tableBody) {
        tableBody.innerHTML = '';
        
        partners.forEach(partner => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${partner.name}</td>
                <td>${getPartnerTypeName(partner.type)}</td>
                <td>${partner.rating}</td>
                <td>${partner.phone || '-'}</td>
                <td>
                    <button class="btn primary edit-btn" data-id="${partner.id}">Изменить</button>
                    <button class="btn danger delete-btn" data-id="${partner.id}">Удалить</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                window.location.href = `partner-form.html?id=${e.target.dataset.id}`;
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                if (confirm('Вы уверены, что хотите удалить этого партнёра?')) {
                    const result = await deletePartner(e.target.dataset.id);
                    if (result.success) {
                        showNotification('Партнёр успешно удалён');
                        await loadPartners();
                    }
                }
            });
        });
    }
}

function getPartnerTypeName(type) {
    const types = {
        'retail': 'Розничный магазин',
        'wholesale': 'Оптовый покупатель',
        'online': 'Интернет-магазин',
        'distributor': 'Дистрибьютор'
    };
    return types[type] || type;
}

async function loadPartnerData(id) {
    const partner = await getPartnerById(id);
    if (partner) {
        document.getElementById('partnerId').value = partner.id;
        document.getElementById('name').value = partner.name;
        document.getElementById('type').value = partner.type;
        document.getElementById('rating').value = partner.rating;
        document.getElementById('address').value = partner.address || '';
        document.getElementById('director').value = partner.director || '';
        document.getElementById('phone').value = partner.phone || '';
        document.getElementById('email').value = partner.email || '';
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        type: document.getElementById('type').value,
        rating: parseInt(document.getElementById('rating').value),
        address: document.getElementById('address').value,
        director: document.getElementById('director').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value
    };
    
    const partnerId = document.getElementById('partnerId').value;
    
    try {
        let result;
        if (partnerId) {
            formData.id = partnerId;
            result = await updatePartner(formData);
        } else {
            result = await addPartner(formData);
        }
        
        if (result) {
            showNotification('Данные успешно сохранены');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        }
    } catch (error) {
        showNotification('Ошибка при сохранении данных', 'error');
        console.error(error);
    }
}

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.className = `notification ${type}`;
        document.getElementById('notificationMessage').textContent = message;
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addPartnerBtn = document.getElementById('addPartnerBtn');
    if (addPartnerBtn) {
        addPartnerBtn.addEventListener('click', function() {
            window.location.href = 'partner-form.html';
        });
    }

});


async function getAllPartners() {
    try {
        const response = await fetch(`${API_URL}?action=getAllPartners`);
        if (!response.ok) throw new Error('Ошибка сети');
        return await response.json();
    } catch (error) {
        console.error('Ошибка при получении партнёров:', error);
        return [];
    }
}

async function getPartnerById(id) {
    try {
        const response = await fetch(`${API_URL}?action=getPartnerById&id=${id}`);
        if (!response.ok) throw new Error('Ошибка сети');
        return await response.json();
    } catch (error) {
        console.error('Ошибка при получении партнёра:', error);
        return null;
    }
}

async function getAllPartners() {
    try {
        const response = await fetch(`${API_URL}?action=getAllPartners`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error('Ошибка при получении партнёров:', error);
        showNotification('Ошибка загрузки данных', 'error');
        return [];
    }
}

async function addPartner(partnerData) {
    try {
        const response = await fetch(`${API_URL}?action=addPartner`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(partnerData)
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.error || `HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Ошибка при добавлении партнёра:', error);
        showNotification(error.message || 'Ошибка при добавлении', 'error');
        throw error;
    }
}
async function updatePartner(partnerData) {
    try {
        const response = await fetch(`${API_URL}?action=updatePartner`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(partnerData)
        });
        if (!response.ok) throw new Error('Ошибка сети');
        return await response.json();
    } catch (error) {
        console.error('Ошибка при обновлении партнёра:', error);
        return null;
    }
}

async function deletePartner(id) {
    try {
        const response = await fetch(`${API_URL}?action=deletePartner&id=${id}`, {
            method: 'DELETE'
        });
        if (!response.ok) throw new Error('Ошибка сети');
        return await response.json();
    } catch (error) {
        console.error('Ошибка при удалении партнёра:', error);
        return { success: false };
    }
}