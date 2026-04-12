// Function to toggle benefit bullets and open login modal
function toggleBenefits(event, cardElement) {
    event.preventDefault();
    event.stopPropagation();
    
    // Check if card is already expanded
    const isExpanded = cardElement.classList.contains('expanded');
    
    if (isExpanded) {
        // If expanded, collapse it
        cardElement.classList.remove('expanded');
    } else {
        // Expand the card to show benefits
        // First, collapse any other expanded cards
        document.querySelectorAll('.feature-card.expanded').forEach(card => {
            card.classList.remove('expanded');
        });
        
        // Expand this card
        cardElement.classList.add('expanded');
    }
}

// Close expanded card when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.feature-card')) {
        document.querySelectorAll('.feature-card.expanded').forEach(card => {
            card.classList.remove('expanded');
        });
    }
});

// Function to open login modal (called after exploring benefits)
function openLoginModal(event) {
    event.preventDefault();
    const loginModal = document.getElementById('login-modal');
    if (loginModal) {
        loginModal.style.display = 'flex';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Get all required elements
    const navLoginBtn = document.querySelector('#nav-login-btn');
    const registerModal = document.getElementById('register-modal');
    const closeRegisterModal = document.getElementById('close-modal');
    const quizForm = document.querySelector('#register-modal #quiz-form');
    const registrationSection = document.getElementById('registration-section');
    const quizSection = document.getElementById('quiz-section');
    const loginModal = document.getElementById('login-modal');
    const closeLoginModal = document.getElementById('close-login-modal');

    // Show login modal when clicking login button in nav
    if (navLoginBtn && loginModal) {
        navLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'flex';
        });
    }
    
    // Add click event to feature cards to open login modal after expanding
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('dblclick', function(e) {
            if (this.classList.contains('expanded')) {
                openLoginModal(e);
            }
        });
    });

    // Handle quiz submission
    if (quizForm) {
        quizForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Make sure all questions are answered
            const totalQuestions = 5;
            const answeredQuestions = quizForm.querySelectorAll('input[type="radio"]:checked').length;
            
            if (answeredQuestions < totalQuestions) {
                alert('Please answer all questions before proceeding.');
                return;
            }

            // Calculate skin type based on answers
            const answers = {
                Oily: 0,
                Dry: 0,
                Normal: 0,
                Combination: 0,
                Sensitive: 0
            };

            const formData = new FormData(quizForm);
            for (let [name, value] of formData.entries()) {
                answers[value]++;
            }

            let skinType = Object.entries(answers).reduce((a, b) => a[1] > b[1] ? a : b)[0];

            document.getElementById('skin-type-input').value = skinType;

            quizSection.style.display = 'none';

            if (registerModal) registerModal.style.display = 'none';

            const resultModal = document.getElementById('result-modal');
            const skinTypeResult = document.getElementById('skin-type-result');
            skinTypeResult.textContent = skinType;
            resultModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            const continueBtn = document.getElementById('continue-to-register');
            if (continueBtn) {
                continueBtn.replaceWith(continueBtn.cloneNode(true));
            }
            const newContinueBtn = document.getElementById('continue-to-register');
            if (newContinueBtn) newContinueBtn.focus();

            newContinueBtn.addEventListener('click', function() {
                resultModal.style.display = 'none';
                document.body.style.overflow = '';

                if (registerModal) registerModal.style.display = 'flex';
                if (registrationSection) registrationSection.style.display = 'block';
                if (quizSection) quizSection.style.display = 'none';

                const firstInput = document.querySelector('#registration-section input');
                if (firstInput) firstInput.focus();
            });

            resultModal.addEventListener('click', function(ev) {
                if (ev.target === resultModal) {
                    resultModal.style.display = 'none';
                    document.body.style.overflow = '';
                    if (quizSection) quizSection.style.display = 'block';
                }
            });

            function onEsc(e) {
                if (e.key === 'Escape' && resultModal.style.display === 'flex') {
                    resultModal.style.display = 'none';
                    document.body.style.overflow = '';
                    if (quizSection) quizSection.style.display = 'block';
                    document.removeEventListener('keydown', onEsc);
                }
            }
            document.addEventListener('keydown', onEsc);
        });
    }

    if (closeRegisterModal && registerModal) {
        closeRegisterModal.addEventListener('click', function() {
            registerModal.style.display = 'none';
            if (quizSection && registrationSection) {
                quizSection.style.display = 'block';
                registrationSection.style.display = 'none';
            }
        });
    }

    if (closeLoginModal && loginModal) {
        closeLoginModal.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });
    }

    const showLogin = document.getElementById('show-login');
    const showRegister = document.getElementById('show-register');

    if (showLogin && registerModal && loginModal) {
        showLogin.addEventListener('click', function(e) {
            e.preventDefault();
            registerModal.style.display = 'none';
            loginModal.style.display = 'flex';
        });
    }

    if (showRegister && registerModal && loginModal) {
        showRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'flex';
            if (quizSection && registrationSection) {
                quizSection.style.display = 'block';
                registrationSection.style.display = 'none';
            }
        });
    }

    if (showRegister && registerModal && loginModal) {
        showRegister.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'flex';
            // Reset to quiz section when switching back to register
            if (quizSection && registrationSection) {
                quizSection.style.display = 'block';
                registrationSection.style.display = 'none';
            }
        });
    }

    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-bg')) {
            e.target.style.display = 'none';
            // Reset to quiz section when closing register modal
            if (e.target === registerModal && quizSection && registrationSection) {
                quizSection.style.display = 'block';
                registrationSection.style.display = 'none';
            }
        }
    });
});