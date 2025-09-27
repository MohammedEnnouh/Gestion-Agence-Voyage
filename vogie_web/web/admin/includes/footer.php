</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        function confirmDelete(message = "Êtes-vous sûr de vouloir supprimer cet élément ?") {
            return confirm(message);
        }

        function showAlert(message, type = "success") {
            const alertDiv = document.createElement("div");
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector(".main-content").insertBefore(alertDiv, document.querySelector(".main-content").firstChild);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const alerts = document.querySelectorAll(".alert");
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.querySelector(".btn-close")) {
                        alert.querySelector(".btn-close").click();
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>