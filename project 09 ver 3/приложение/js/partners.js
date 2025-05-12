
function getAllPartners() {
    return partners;
}

function getPartnerById(id) {
    return partners.find(partner => partner.id === id);
}

function addPartner(partnerData) {
    const newId = partners.length > 0 ? Math.max(...partners.map(p => p.id)) + 1 : 1;
    const newPartner = { id: newId, ...partnerData };
    partners.push(newPartner);
    return newPartner;
}

function updatePartner(id, partnerData) {
    const index = partners.findIndex(partner => partner.id === id);
    if (index !== -1) {
        partners[index] = { ...partners[index], ...partnerData };
        return partners[index];
    }
    return null;
}

function deletePartner(id) {
    partners = partners.filter(partner => partner.id !== id);
}