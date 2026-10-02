    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.querySelectorAll('.alert.fade.show').forEach(function(alert) {
    setTimeout(function() {
        var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if(bsAlert) bsAlert.close();
    }, 4000);
});


document.querySelectorAll('.btn-delete-confirm').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        if(!confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
