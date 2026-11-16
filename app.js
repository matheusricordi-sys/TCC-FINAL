// Modal functions
function openModal(id) {
    document.getElementById(id)?.classList.add('show');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('show');
}

// Close modal when clicking outside
window.onclick = (e) => {
    if (e.target.classList?.contains('modal')) {
        e.target.classList.remove('show');
    }
};

// Toggle norm details
document.querySelectorAll('.norm').forEach(n => {
    const toggleNorm = () => {
        const isOpen = n.classList.toggle('open');
        n.setAttribute('aria-expanded', isOpen);
    };

    n.addEventListener('click', toggleNorm);
    n.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleNorm();
        }
    });
});

// Prevent form submission in modals for demo
document.addEventListener('submit', function(e) {
    if (e.target.closest('.modal')) {
        // Deixar passar normalmente em produção
    }
});
