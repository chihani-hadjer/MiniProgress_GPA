    </main>
</div>
<script>
// Auto-dismiss alerts after 5s
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(() => { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 500); }, 5000);
    const cls = el.querySelector('.btn-close');
    if (cls) cls.addEventListener('click', () => el.remove());
});
</script>
</body>
</html>
