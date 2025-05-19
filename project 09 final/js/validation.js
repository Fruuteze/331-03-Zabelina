export function validatePartnerForm() {
    let isValid = true;
    
    const name = document.getElementById('name').value.trim();
    if (!name) {
        document.getElementById('nameError').textContent = 'Название обязательно';
        isValid = false;
    } else {
        document.getElementById('nameError').textContent = '';
    }
    
    const type = document.getElementById('type').value;
    if (!type) {
        isValid = false;
    }
    
    const rating = document.getElementById('rating').value;
    if (!rating || isNaN(rating) || rating < 0) {
        document.getElementById('ratingError').textContent = 'Введите корректный рейтинг';
        isValid = false;
    } else {
        document.getElementById('ratingError').textContent = '';
    }
    
    const phone = document.getElementById('phone').value.trim();
    if (phone) {
        const phoneRegex = /^\+7\s?[\(]?\d{3}[\)]?\s?\d{3}[-]?\d{2}[-]?\d{2}$/;
        if (!phoneRegex.test(phone)) {
            document.getElementById('phoneError').textContent = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
            isValid = false;
        } else {
            document.getElementById('phoneError').textContent = '';
        }
    }
    
    const email = document.getElementById('email').value.trim();
    if (email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('emailError').textContent = 'Введите корректный email';
            isValid = false;
        } else {
            document.getElementById('emailError').textContent = '';
        }
    }
    
    return isValid;
}