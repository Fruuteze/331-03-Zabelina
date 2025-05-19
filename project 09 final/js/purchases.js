document.addEventListener('DOMContentLoaded', async () => {
    const partnerId = new URLSearchParams(window.location.search).get('id');
    if (!partnerId) {
        alert('Не указан партнёр');
        window.close();
        return;
    }
    
    document.getElementById('partnerId').value = partnerId;
    
    const response = await fetch(`db.php?action=getPartnerById&id=${partnerId}`);
    const partner = await response.json();
    document.getElementById('currentDiscount').textContent = partner.current_discount || 0;
    
    document.getElementById('purchaseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('amount').value);
        if (isNaN(amount) || amount <= 0) {
            alert('Введите корректную сумму');
            return;
        }
        
        try {
            const response = await fetch('db.php?action=addPartnerPurchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    partnerId,
                    amount,
                    products: [] 
                })
            });
            
            const result = await response.json();
            alert(`Закупка добавлена. Новая скидка: ${result.newDiscount}%`);
            window.close();
        } catch (error) {
            console.error(error);
            alert('Ошибка при сохранении');
        }
    });
});