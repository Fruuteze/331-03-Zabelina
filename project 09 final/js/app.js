document.addEventListener('DOMContentLoaded', async () => {
    if (document.getElementById('partnersTable')) {
        await loadPartners();

        document.getElementById('addPartnerBtn')?.addEventListener('click', () => {
            window.location.href = 'partner-form.html';
        });

                document.getElementById('discount')?.addEventListener('click', () => {
            window.location.href = 'purchase-form.html';
        });

                        document.getElementById('historySales')?.addEventListener('click', () => {
            window.location.href = 'sales-history.html';
        });

    }

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
    try {
        console.log('Loading partner data for ID:', id); 
        
        const partner = await getPartnerById(id);
        console.log('Received partner data:', partner); 

        if (!partner || partner.error) {
            throw new Error(partner?.error || 'Данные партнёра не получены');
        }

        document.getElementById('partnerId').value = partner.id || '';
        document.getElementById('name').value = partner.name || '';
        document.getElementById('type').value = partner.type || '';
        document.getElementById('rating').value = partner.rating ?? '';
        document.getElementById('address').value = partner.address || '';
        document.getElementById('director').value = partner.director || '';
        document.getElementById('phone').value = partner.phone || '';
        document.getElementById('email').value = partner.email || '';

    } catch (error) {
        console.error('Error in loadPartnerData:', error);
        showNotification(error.message || 'Ошибка загрузки данных', 'error');
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

    const partnerIdValue = document.getElementById('partnerId').value;
    if (partnerIdValue) {
        formData.id = parseInt(partnerIdValue);
    }
    
    try {
        let result;
        if (formData.id) {
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
        showNotification(error.message || 'Ошибка при сохранении данных', 'error');
        console.error(error);
    }
}

async function updatePartner(partnerData) {
    try {
        const response = await fetch(`${API_URL}?action=updatePartner`, {
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
        console.error('Ошибка при обновлении партнёра:', error);
        showNotification(error.message || 'Ошибка при обновлении', 'error');
        throw error;
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

const API_URL = 'db.php';


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
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.error || `Ошибка сервера: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        if (!data) {
            throw new Error('Пустой ответ от сервера');
        }

        return data;
    } catch (error) {
        console.error('Ошибка при получении партнёра:', error);
        showNotification(error.message || 'Ошибка загрузки данных', 'error');
        throw error;
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
