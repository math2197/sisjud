            <?php if (isset($_SESSION['user_id'])): ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Função para validar formulários com CSRF token
    function validateForm(form) {
        const csrfToken = form.querySelector('input[name="csrf_token"]');
        if (!csrfToken || !csrfToken.value) {
            alert('Erro de segurança: Token CSRF não encontrado');
            return false;
        }
        return true;
    }

    // Adiciona validação CSRF a todos os formulários
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });
        });
    });
    </script>
</body>
</html> 